<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function testWrongRegisterRequest()
    {
        $response = $this->post('/api/auth/register', [
            'username' => 'carlos',
            'email' => 'carlos',
            'password' => 'carlos',
            'password_confirmation' => 'carlos',
        ]);

        $response->assertJsonValidationErrors(['email']);
    }

    public function testRegisteringUser()
    {
        $response = $this->post('/api/auth/register', [
            'username' => 'carlos',
            'email' => 'carlos@gmail.com',
            'password' => 'carlos100',
            'password_confirmation' => 'carlos100',
        ]);
        $response->assertCookie('monody_access_token');
        $response->assertCreated();

        $this->assertTrue(User::where('username', 'carlos')->exists());
    }

    public function testWrongLoginRequest()
    {
        $response = $this->post('/api/auth/login', [
            'username' => 'carlos',
            'password' => 'carl',
            'remember_me' => false,
        ]);

        $response->assertJsonValidationErrors(['password']);
    }

    public function testRegisteringExistantUser()
    {
        $response = $this->post('/api/auth/register', [
            'username' => 'JohnTest',
            'email' => 'john.test10@gmail.com',
            'password' => 'azertyuiop',
            'password_confirmation' => 'azertyuiop',
        ]);

        $response->assertJsonValidationErrors(['username']);
    }

    public function testLoginWithWrongPassword()
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
        ]);

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
