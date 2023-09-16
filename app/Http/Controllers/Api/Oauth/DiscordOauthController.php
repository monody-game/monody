<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Discord\Provider;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class DiscordOauthController extends Controller
{
    use OauthProviderTrait;

    private const API_ENDPOINT = 'https://discord.com/api/v10';

    public function link(): RedirectResponse
    {
        /** @var Provider $provider */
        $provider = $this->generateProvider('discord', ['identify', 'email', 'role_connections.write']);

        return $provider
            ->withConsent()
            ->redirect();
    }

    public function user(Request $request): JsonApiResponse
    {
        /** @var User $user Route is protected by auth guard */
        $user = $request->user();

        if ($user->discord_id === null || $user->discord_token === null || $user->discord_refresh_token === null) {
            return new JsonApiResponse(['message' => 'You need to link your discord account first'], status: Status::BAD_REQUEST);
        }

        /** @var Provider $driver */
        $driver = Socialite::driver('discord');

        try {
            $user = $driver->userFromToken($user->discord_token);
        } catch (ClientException $e) {
            $payload = $this->refreshAccessToken($user->discord_refresh_token);
            $user->discord_token = $payload['access_token'];
            $user->discord_refresh_token = $payload['refresh_token'];
            $user->save();
            $user = $driver->userFromToken($payload['access_token']);
        }

        return JsonApiResponse::make([
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
            ],
        ])->withCache(Carbon::now()->addHour());
    }

    public function check(Request $request): RedirectResponse|JsonApiResponse
    {
        if (!$request->has('code')) {
            return new JsonApiResponse(['message' => 'GET parameter "code" is mandatory.'], Status::BAD_REQUEST);
        }

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('discord');
            $discordUser = $driver->stateless()->user();
        } catch (Exception) {
            return new JsonApiResponse(['message' => 'An unexpected error occurred, try to relog.'], Status::BAD_REQUEST);
        }

        $discordId = config('services.discord.client_id');

        /** @var User $user behind api guard */
        $user = $request->user();

        $user->avatar = $user->avatar === '/images/avatar/default.png' && $discordUser->getAvatar() !== null ? $discordUser->getAvatar() : $user->avatar;
        $user->discord_linked_at = Carbon::now();
        $user->discord_id = $discordUser->getId();
        $user->discord_token = $discordUser->accessTokenResponseBody['access_token'];
        $user->discord_refresh_token = $discordUser->accessTokenResponseBody['refresh_token'];

        $user->save();

        /** @var Response $res */
        $res = Http::withToken($discordUser->accessTokenResponseBody['access_token'])
            ->asJson()
            ->put(
                self::API_ENDPOINT . "/users/@me/applications/{$discordId}/role-connection",
                [
                    'plateform_name' => 'Monody',
                    'metadata' => [
                        'account_linked' => 1,
                    ],
                ]
            );

        if ($res->successful()) {
            // We force a cache flush for endpoint /user
            return new RedirectResponse('/play?flush=/user');
        }

        return new JsonApiResponse([
            'message' => 'An error occurred, please retry.',
        ]);
    }

    public function unlink(Request $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();
        $discordId = config('services.discord.client_id');

        if (!$user->discord_id) {
            return new JsonApiResponse(['message' => 'Your Discord account is not linked to Monody.'], Status::FORBIDDEN);
        }

        /** @var string $token */
        $token = $user->discord_token;

        Http::withToken($token)
            ->asJson()
            ->put(
                "https://discord.com/api/v10/users/@me/applications/{$discordId}/role-connection",
                [
                    'plateform_name' => 'Monody',
                    'metadata' => [
                        'account_linked' => 0,
                    ],
                ]
            );

        $user->discord_id = null;
        $user->discord_token = null;
        $user->discord_refresh_token = null;
        $user->discord_linked_at = null;
        $user->save();

        return JsonApiResponse::make(status: Status::NO_CONTENT)
            ->flushCacheFor('/user');
    }

    private function refreshAccessToken(string $refreshToken): array
    {
        $url = self::API_ENDPOINT . '/oauth2/token';
        $data = [
            'client_id' => config('services.discord.client_id'),
            'client_secret' => config('services.discord.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        $res = Http::asForm()
            ->post($url, $data);

        return $res->json();
    }
}
