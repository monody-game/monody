<?php

namespace Tests\Unit\Http\Controllers\Api\Auth;

use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    public function testWrongRegisterRequest()
    {
        $response = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/auth/register', [
                'username' => 'carlos',
                'email' => 'carlos',
                'password' => 'carlos',
                'password_confirmation' => 'carlos',
            ]);

        $response->assertJsonValidationErrors(['email']);
    }

    public function testRegisteringUser()
    {
        $response = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/auth/register', [
                'username' => 'carlos',
                'email' => 'carlos@gmail.com',
                'password' => 'carlos100',
                'password_confirmation' => 'carlos100',
            ]);
        $response->assertCookie('monody_access_token');
        $response->assertCreated();

        $this->assertTrue(User::where('username', 'carlos')->exists());
    }

    public function testRegisteringExistantUser()
    {
        $response = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/auth/register', [
                'username' => 'JohnTest',
                'email' => 'john.test10@gmail.com',
                'password' => 'azertyuiop',
                'password_confirmation' => 'azertyuiop',
            ]);

        $response->assertJsonValidationErrors(['username']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/auth/register', [
                'username' => 'JohnTest',
                'email' => 'john.test@gmail.com',
                'password' => 'johntest',
                'password_confirmation' => 'johntest',
            ]);
    }
}
