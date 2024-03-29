<?php

namespace Tests\Unit\Http\Controllers\Api\Auth;

use App\Http\Middleware\RestrictRequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    public function testWrongLoginRequest()
    {
        $this
            ->post('/api/auth/login', [
                'username' => 'carlos',
                'password' => 'carl',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function testLoginWithWrongPassword()
    {
        $response = $this->post('/api/auth/login', [
            'username' => 'carlos',
            'password' => 'carlos',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonPath('data.message', 'Invalid credentials.');
        $response->assertCookieMissing('monody_access_token');
    }

    public function testLogin()
    {
        $response = $this->post('/api/auth/login', [
            'username' => 'JohnTest',
            'password' => 'johntest',
        ])->assertOk();

        $response->assertCookie('monody_access_token');
        $this->assertAuthenticated();
    }

    public function testLoginWithEmail()
    {
        $this
            ->post('/api/auth/login', [
                'username' => 'john.test@gmail.com',
                'password' => 'johntest',
            ])
            ->assertOk()
            ->assertCookie('monody_access_token');
        $this->assertAuthenticated();
    }

    public function testLogout()
    {
        $response = $this
            ->post('/api/auth/login', [
                'username' => 'JohnTest',
                'password' => 'johntest',
            ])
            ->assertCookie('monody_access_token');

        $cookie = $response->getCookie('monody_access_token', false, true);

        $this
            ->withCookie($cookie->getName(), $cookie->getValue())
            ->post('/api/auth/logout')
            ->assertOk()
            ->assertCookieMissing('monody_access_token');
    }

    public function testLoginWhileAlreadyBeingLoggedIn()
    {
        $response = $this->post('/api/auth/login', [
            'username' => 'JohnTest',
            'password' => 'johntest',
        ])->assertOk();

        $response->assertCookie('monody_access_token');
        $token = $response->getCookie('monody_access_token', false)->getValue();

        $response = $this
            ->withCookie('monody_access_token', $token)
            ->post('/api/auth/login', [
                'username' => 'second user',
                'password' => '12345678',
            ])
            ->assertOk();

        $response->assertCookie('monody_access_token');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->withoutMiddleware(RestrictRequest::class)
            ->post('/api/auth/register', [
                'username' => 'JohnTest',
                'email' => 'john.test@gmail.com',
                'password' => 'johntest',
                'password_confirmation' => 'johntest',
            ]);

        $this
            ->withoutMiddleware(RestrictRequest::class)
            ->post('/api/auth/register', [
                'username' => 'second user',
                'email' => 'second.user@gmail.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
            ]);
    }
}
