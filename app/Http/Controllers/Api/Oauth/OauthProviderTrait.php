<?php

namespace App\Http\Controllers\Api\Oauth;

use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

trait OauthProviderTrait
{
    /**
     * @param  string[]  $scopes
     */
    public function generateProvider(string $provider, array $scopes): AbstractProvider
    {
        return Socialite::driver($provider)
            ->stateless()
            ->scopes($scopes);
    }
}
