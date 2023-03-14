<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testGettingCurrentUser()
    {
        $this
            ->actingAs($this->user, 'api')
            ->get('/api/user')
            ->assertJson($this->user->toArray());
    }

    public function testUpdatingUser()
    {
        $user = User::factory()->makeOne([
            'username' => 'John',
            'email' => 'testemail@test.com',
        ]);

        $this
            ->actingAs($user, 'api')
            ->patch('/api/user', [
                'email' => 'emailtest@test.com',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $user->id,
                'username' => 'John',
                'email' => 'emailtest@test.com',
            ]);

        $user = $user->refresh();

        $this->assertSame('John', $user->username);
        $this->assertSame('emailtest@test.com', $user->email);
    }

    public function testGettingUserByDiscordId()
    {
        $linkedUser = User::factory()->create(['discord_id' => 1234]);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->get('/api/user/discord/56789')
            ->assertUnauthorized();

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->get('/api/user/discord/1234')
            ->assertJson([
                $linkedUser->fresh()->toArray(),
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
}
