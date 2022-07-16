<?php

namespace Tests\Unit\Http\Controllers\Api\Auth;

use App\Models\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
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
	}

	public function testLoginWithWrongPassword()
	{
		$response = $this->post('/api/auth/login', [
			'username' => 'carlos',
			'password' => 'carlos',
			'remember_me' => false,
		]);

		$response->assertStatus(Response::HTTP_UNAUTHORIZED);
		$response->assertJson(['message' => 'Invalid Credentials']);
		$response->assertCookieMissing('monody_access_token');
	}

	public function testLogin()
	{
		$response = $this->post('/api/auth/login', [
			'username' => 'JohnTest',
			'password' => 'johntest',
			'remember_me' => false,
		])->assertStatus(Response::HTTP_NO_CONTENT);

		$response->assertCookie('monody_access_token');
		$this->assertAuthenticated();
	}

	public function testLogout()
	{
		$response = $this->post('/api/auth/login', [
			'username' => 'JohnTest',
			'password' => 'johntest',
			'remember_me' => false,
		]);

		$response->assertCookie('monody_access_token');

		$user = User::where('username', 'JohnTest')->get()->first();

		$response = $this
			->actingAs($user, 'api')
			->post('/api/auth/logout')
			->assertStatus(200);

		$response->assertCookieMissing('monody_access_token');
	}

	public function testLoginWhileAlreadyBeingLoggedIn() {
		$response = $this->post('/api/auth/login', [
			'username' => 'JohnTest',
			'password' => 'johntest',
			'remember_me' => false,
		])->assertStatus(Response::HTTP_NO_CONTENT);

		$response->assertCookie('monody_access_token');
		$token = $response->getCookie('monody_access_token', false)->getValue();

		$response = $this
			->withCookie("monody_access_token", $token)
			->post('/api/auth/login', [
			'username' => 'second user',
			'password' => '123456',
			'remember_me' => false,
		])
			->assertStatus(Response::HTTP_NO_CONTENT);

		$response->assertCookie('monody_access_token');
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

		$this->post('/api/auth/register', [
			'username' => 'second user',
			'email' => 'second.user@gmail.com',
			'password' => '123456',
			'password_confirmation' => '123456',
		]);
	}
}
