<?php

namespace App\Http\Controllers\Api\Oauth;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class GoogleOauthController extends AbstractOauthController
{
    public function link(): RedirectResponse
    {
        return $this->generateProvider('google', ['openid', 'https://www.googleapis.com/auth/userinfo.email'])->redirect();
    }

    public function check(Request $request): never
    {
        dd($request->all());
    }
}
