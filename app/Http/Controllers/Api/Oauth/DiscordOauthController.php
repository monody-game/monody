<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class DiscordOauthController extends Controller
{
    use OauthProviderTrait;

    public function link(): RedirectResponse
    {
        return $this->generateProvider('discord', ['identify', 'email', 'role_connections.write'])->redirect();
    }

    public function check(Request $request): RedirectResponse|JsonResponse
    {
        if (!$request->has('code')) {
            return new JsonResponse([
                'message' => 'GET parameter "code" is needed',
                'data' => $request->all(),
            ]);
        }

        try {
            $discordUser = Socialite::driver('discord')->stateless()->user();
        } catch (Exception) {
            return new JsonResponse(['An error occurred, try to relog'], Response::HTTP_BAD_REQUEST);
        }

        $discordId = config('services.discord.client_id');

        $user = User::updateOrCreate(['email' => $discordUser->email], [
            'discord_id' => $discordUser->getId(),
            'discord_token' => $discordUser->accessTokenResponseBody['access_token'],
            'discord_refresh_token' => $discordUser->accessTokenResponseBody['refresh_token'],
        ]);

        $user->avatar = '/images/avatar/default.png' === $user->avatar ? $discordUser->getAvatar() : $user->avatar;

        /** @var \Illuminate\Http\Client\Response $res */
        $res = Http::withToken($discordUser->accessTokenResponseBody['access_token'])
            ->asJson()
            ->put(
                "https://discord.com/api/v10/users/@me/applications/{$discordId}/role-connection",
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

        return new JsonResponse(['An error occurred'], Response::HTTP_BAD_REQUEST);
    }

    public function unlink(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $discordId = config('services.discord.client_id');

        if (!$user->discord_id) {
            return new JsonResponse(['error' => 'Your Discord account is not linked'], Response::HTTP_FORBIDDEN);
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
        $user->save();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
