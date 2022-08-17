<?php

namespace Http\Controllers\Api\Oauth;

use App\Http\Controllers\Api\Oauth\OauthProviderTrait;
use SocialiteProviders\Discord\Provider;
use Tests\TestCase;

class OauthProviderTraitTest extends TestCase
{
    use OauthProviderTrait;

    public function testGeneratingDiscordProvider()
    {
        $scopes = ['identify', 'email'];
        $provider = $this->generateProvider('discord', $scopes);

        $this->assertInstanceOf(Provider::class, $provider);
        $this->assertSame($scopes, $provider->getScopes());
    }
}
