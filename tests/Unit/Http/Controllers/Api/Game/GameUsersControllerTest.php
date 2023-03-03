<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\Roles;
use App\Models\User;
use App\Traits\InteractsWithRedis;
use Tests\TestCase;

class GameUsersControllerTest extends TestCase
{
    use InteractsWithRedis;

    private User $user;

    private User $secondUser;

    public function testListUsers()
    {
        $this->actingAs($this->user, 'api')
            ->get("/api/game/{$this->game['id']}/users")
            ->assertJson([
                'users' => [
                    $this->user['id'],
                ],
            ]);
    }

    public function testGettingUserRole(): void
    {
        $this->secondUser->current_game = 'id';
        $this->secondUser->save();

        $this->redis()->set('game:id', [
            'assigned_roles' => [$this->secondUser->id => 1],
            'dead_users' => [$this->secondUser->id],
        ]);

        $response = $this->actingAs($this->secondUser, 'api')
            ->call('GET', "/api/game/user/{$this->secondUser->id}/role")
            ->assertOk()
            ->json();

        $this->assertSame(Roles::from(1)->full(), $response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->secondUser] = User::factory(2)->create();

        $this->game = $this->actingAs($this->user, 'api')->put('/api/game', [
            'roles' => [1, 2],
            'users' => [],
        ])['game'];
    }
}
