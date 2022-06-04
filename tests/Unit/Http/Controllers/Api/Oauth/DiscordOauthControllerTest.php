<?php

namespace Http\Controllers\Api\Oauth;

use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DiscordOauthControllerTest extends TestCase
{
	public function testDiscordOauthLink(): void
	{
		$response = $this->get('/api/oauth/link/discord')
			->assertStatus(Response::HTTP_FOUND);

		if (file_exists(dirname(__DIR__, 6) . DIRECTORY_SEPARATOR . '.env')) {
			$response->assertRedirect(
				'https://discord.com/api/oauth2/authorize?client_id=' . env('DISCORD_CLIENT_ID') .
				'&redirect_uri=' . urlencode(env('DISCORD_REDIRECT_URI')) .
				'&scope=identify+email&response_type=code');
		} else {
			$response->assertRedirect(
				'https://discord.com/api/oauth2/authorize?scope=identify+email&response_type=code');
		}
	}
}
