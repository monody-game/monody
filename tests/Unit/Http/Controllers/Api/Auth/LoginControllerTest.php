<?php

namespace Http\Controllers\Api\Auth;

use Tests\TestCase;

class LoginControllerTest extends TestCase
{
	public function testWrongLoginRequest()
	{
		$response = $this->post('/api/auth/login', [
			'username' => 'carlos',
			'password' => 'carl',
			'remember_me' => false,
		]);

		$response->assertJsonValidationErrors(['password']);
	}public function testLoginWithWrongPassword()
{
	$response = $this->post('/api/auth/login', [
		'username' => 'carlos',
		'password' => 'carlos',
		'remember_me' => false,
	]);

	$response->assertStatus(401);
	$response->assertJson(['message' => 'Invalid Credentials']);
	$response->assertCookieMissing('monody_access_token');
}

	public function testLogin()
	{
		$response = $this->post('/api/auth/login', [
			'username' => 'JohnTest',
			'password' => 'johntest',
			'remember_me' => false,
		])->assertStatus(204);

		$response->assertCookie('monody_access_token');
	}

	public function testLogout()
	{
		$response = $this->post('/api/auth/login', [
			'username' => 'JohnTest',
			'password' => 'johntest',
			'remember_me' => false,
		]);

		$response->assertCookie('monody_access_token');

		$response = $this->post('/api/auth/logout');

		$response->assertCookieMissing('monody_access_token');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->post('/api/auth/register', [
			'username' => 'JohnTest',
			'email' => 'john.test@gmail.com',
			'password' => 'johntest',
			'password_confirmation' => 'johntest',
		]);
	}
}
