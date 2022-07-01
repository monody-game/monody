<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class GoogleOauthController extends Controller
{
    use OauthProviderTrait;

    public function link(): RedirectResponse
    {
        return $this->generateProvider('google', ['openid', 'https://www.googleapis.com/auth/userinfo.email'])->redirect();
    }

    public function check(Request $request): void
    {
        // TODO: implement
    }

    public function unlink(Request $request): void
    {
        // TODO: implement
    }
}
