<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class DiscordOauthController extends AbstractOauthController
{
    public function link(): RedirectResponse
    {
        return $this->generateProvider('discord', ['identify', 'email'])->redirect();
    }

    public function check(Request $request): RedirectResponse|JsonResponse
    {
        if (!$request->has('code')) {
            return new JsonResponse([
                'message' => 'An error happened',
                'data' => $request->all()
            ]);
        }

        $discordUser = Socialite::driver('discord')->stateless()->user();

        $user = User::updateOrCreate(['email' => $discordUser->email], [
            'discord_id' => $discordUser->getId(),
            'discord_token' => $discordUser->accessTokenResponseBody['access_token'],
            'discord_refresh_token' => $discordUser->accessTokenResponseBody['refresh_token'],
        ]);

        $user->avatar = '/images/avatar/default.png' === $user->avatar ? $discordUser->getAvatar() : $user->avatar;

        try {
            Http::post('bot/linked', [
                'discord_user_id' => $discordUser->getId()
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        Auth::login($user);

        return new RedirectResponse('/play');
    }
}
