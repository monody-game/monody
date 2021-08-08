<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OauthController extends Controller
{
    public function discord(Request $request): RedirectResponse
    {
        return Socialite::driver('discord')->stateless()->scopes(['identify', 'email'])->redirect();
    }

    public function google(Request $request): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->scopes(['openid', 'https://www.googleapis.com/auth/userinfo.email'])->redirect();
    }
}
