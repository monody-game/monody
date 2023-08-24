<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\GameType;
use App\Enums\Role;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Tests\TestCase;

class GameUsersControllerTest extends TestCase
{
    private User $user;

    private User $secondUser;

    public function testListUsers()
    {
        $this->actingAs($this->user, 'api')
            ->get("/api/game/{$this->game['id']}/users")
            ->assertJson([
                'data' => [
                    'users' => [
                        $this->user['id'],
                    ],
                ],
            ]);
    }

    public function testGettingUserRole(): void
    {
        $this->secondUser->current_game = 'id';
        $this->secondUser->save();

        Redis::set('game:id', [
            'assigned_roles' => [$this->secondUser->id => 1],
            'dead_users' => [$this->secondUser->id => []],
        ]);

        $response = $this->actingAs($this->secondUser, 'api')
            ->call('GET', "/api/game/id/user/{$this->secondUser->id}/role")
            ->assertOk()
            ->json('data.role');

        $this->assertSame(Role::from(1)->full(), $response);
    }

    public function testRandomCoupleEndpoint()
    {
        $this->user = User::factory()->createOne();

        $game = $this->actingAs($this->user, 'api')->put('/api/game', [
            'roles' => [Role::Hunter->value, Role::Investigator->value, Role::Werewolf->value],
            'users' => ['boi1', 'boi2', 'boi3'],
            'type' => GameType::NORMAL->value | GameType::RANDOM_COUPLE->value,
        ])->json('data.game');

        $this->assertArrayNotHasKey('couple', $game);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/couple', [
                'gameId' => $game['id'],
            ]);

        $game = Redis::get("game:{$game['id']}");
        $this->assertArrayHasKey('couple', $game);
        $this->assertCount(2, $game['couple']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->secondUser] = User::factory(2)->create();

        $this->game = $this->actingAs($this->user, 'api')->put('/api/game', [
            'roles' => [1, 2, 3, 4, 5],
            'users' => [],
        ])->json('data.game');
    }
}
