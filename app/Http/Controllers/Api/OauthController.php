<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OauthController extends Controller
{
    public function discordLink(): RedirectResponse
    {
        return Socialite::driver('discord')->stateless()->scopes(['identify', 'email'])->redirect();
    }

    public function discordCheck(Request $request): JsonResponse
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

        return new JsonResponse([], 204);
    }

    public function googleLink(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->scopes(['openid', 'https://www.googleapis.com/auth/userinfo.email'])->redirect();
    }

    public function googleCheck(Request $request): never
    {
        dd($request->all());
    }
}
