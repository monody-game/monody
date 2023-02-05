<?php

namespace Tests\Unit\Http\Controllers\Api\Auth;

use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\Statistic;
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
        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/auth/register', [
                'username' => 'JohnTest',
                'email' => 'john.test10@gmail.com',
                'password' => 'azertyuiop',
                'password_confirmation' => 'azertyuiop',
            ])
            ->assertJsonValidationErrors(['username']);
    }

    public function testSettingDefaultStatsWhenRegisteringUser()
    {
        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/auth/register', [
                'username' => 'TestUser',
                'email' => 'test@monody.fr',
                'password' => 'test1234',
                'password_confirmation' => 'test1234',
            ])
            ->assertCreated();

        $userId = User::where('username', 'TestUser')->first()->id;

        $this->assertSame([
            'user_id' => $userId,
            'win_streak' => 0,
            'longest_streak' => 0,
        ], Statistic::where('user_id', $userId)->first()->toArray());
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
