<?php

namespace App\Http\Controllers\Api\Oauth;

use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

trait OauthProviderTrait
{
    /**
     * @param  string[]  $scopes
     */
    public function generateProvider(string $name, array $scopes): AbstractProvider
    {
        /** @var AbstractProvider $provider */
        $provider = Socialite::driver($name);

        return $provider->stateless()
            ->with(['prompt' => 'consent'])
            ->scopes($scopes);
    }
}
