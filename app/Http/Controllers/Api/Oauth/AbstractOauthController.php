<?php

namespace App\Http\Controllers\Api\Oauth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

abstract class AbstractOauthController extends Controller
{
    /**
     * @param string[] $scopes
     */
    public function generateProvider(string $provider, array $scopes): AbstractProvider
    {
        return Socialite::driver($provider)
            ->stateless()
            ->scopes($scopes);
    }
}
