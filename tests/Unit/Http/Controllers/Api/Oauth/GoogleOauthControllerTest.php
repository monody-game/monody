<?php

namespace Tests\Unit\Http\Controllers\Api\Oauth;

use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GoogleOauthControllerTest extends TestCase
{
	public function testGoogleOauthLink(): void
	{
		$response = $this->get('/api/oauth/link/google')
			->assertStatus(Response::HTTP_FOUND);

		if (file_exists(dirname(__DIR__, 6) . DIRECTORY_SEPARATOR . '.env')) {
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
