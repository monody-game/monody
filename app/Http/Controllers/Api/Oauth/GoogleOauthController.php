<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleOauthController extends Controller
{
    public function link(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->scopes(['openid', 'https://www.googleapis.com/auth/userinfo.email'])->redirect();
    }

    public function check(Request $request): never
    {
        dd($request->all());
    }
}
