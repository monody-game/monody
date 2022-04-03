<?php

namespace Http\Controllers\Api;

use Tests\TestCase;

class OauthControllerTest extends TestCase
{
    public function testDiscordOauthLink(): void
    {
        $response = $this->get('/api/oauth/link/discord');

        $response->assertStatus(302);

        if (file_exists(dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . '.env')) {
            $response->assertRedirect(
                'https://discord.com/api/oauth2/authorize?client_id=' . env('DISCORD_CLIENT_ID') .
                '&redirect_uri=' . urlencode(env('DISCORD_REDIRECT_URI')) .
                '&scope=identify+email&response_type=code');
        } else {
            $response->assertRedirect(
                'https://discord.com/api/oauth2/authorize?scope=identify+email&response_type=code');
        }
    }

    public function testGoogleOauthLink(): void
    {
        $response = $this->get('/api/oauth/link/google');

        $response->assertStatus(302);

        if (file_exists(dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . '.env')) {
            $response->assertRedirect(
                'https://accounts.google.com/o/oauth2/auth?client_id=' . env('GOOGLE_CLIENT_ID') .
                '&redirect_uri=' . urlencode(env('GOOGLE_REDIRECT_URI')) .
                '&scope=openid+profile+email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&response_type=code');
        } else {
            $response->assertRedirect(
                'https://accounts.google.com/o/oauth2/auth?scope=openid+profile+email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&response_type=code');
        }
    }
}
