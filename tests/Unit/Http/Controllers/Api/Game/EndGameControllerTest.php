<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Tests\TestCase;

class EndGameControllerTest extends TestCase
{
    private User $user;

    private User $secondUser;

    private array $game;

    public function testCheckingIfAGameCanEnd()
    {
        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/end/check', [
                'gameId' => $this->game['id'],
            ])
            ->assertForbidden();

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => array_merge($this->user->toArray(), ['is_dead' => true])],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/end/check', [
                'gameId' => $this->game['id'],
            ])
            ->assertNoContent();
    }

    protected function setUp(): void
    {
        parent::setUp();

        [$this->user, $this->secondUser] = User::factory(2)->create();

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'roles' => [1, 2],
                'users' => [$this->user->id, $this->secondUser->id],
            ])
            ->json('game');

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        $additionnalKeys = [
            'assigned_roles' => [
                $this->secondUser->id => 1,
                $this->user->id => 2,
            ],
            'is_started' => true,
        ];

        Redis::set("game:{$this->game['id']}", array_merge(Redis::get("game:{$this->game['id']}"), $additionnalKeys));
    }
}
