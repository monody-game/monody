<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class OauthController extends Controller
{
    public function discord(Request $request)
    {
        return Socialite::driver('discord')->stateless()->scopes(['identify', 'email'])->redirect();
    }

    public function google(Request $request)
    {
        return Socialite::driver('google')->stateless()->scopes(['openid', 'https://www.googleapis.com/auth/userinfo.email'])->redirect();
    }
}
