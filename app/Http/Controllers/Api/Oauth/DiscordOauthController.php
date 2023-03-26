<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class DiscordOauthController extends Controller
{
    use OauthProviderTrait;

    private const API_ENDPOINT = 'https://discord.com/api/v10';

    public function link(): RedirectResponse
    {
        return $this->generateProvider('discord', ['identify', 'email', 'role_connections.write'])->redirect();
    }

    public function user(Request $request): JsonApiResponse
    {
        /** @var User $user Route is protected by auth guard */
        $user = $request->user();

        if ($user->discord_id === null || $user->discord_token === null) {
            return new JsonApiResponse(['message' => 'You need to link your discord account first']);
        }

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('discord');

        $user = $driver->userFromToken($user->discord_token);
        // TODO: Handle case where token needs to be refreshed
        //$token = $this->getToken($user->discord_refresh_token);

        return new JsonApiResponse(['user' => [
            'id' => $user->getId(),
            'username' => $user->getNickname(),
            'avatar' => $user->getAvatar(),
        ]]);
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

        $user = User::updateOrCreate(['email' => $discordUser->email], [
            'discord_id' => $discordUser->getId(),
            'discord_token' => $discordUser->accessTokenResponseBody['access_token'],
            'discord_refresh_token' => $discordUser->accessTokenResponseBody['refresh_token'],
        ]);

        $user->avatar = '/images/avatar/default.png' === $user->avatar && $discordUser->getAvatar() !== null ? $discordUser->getAvatar() : $user->avatar;
        $user->discord_linked_at = Carbon::now();

        $user->save();

        /** @var \Illuminate\Http\Client\Response $res */
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
            Auth::login($user);

            return new RedirectResponse('/play');
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

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }
}
