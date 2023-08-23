<?php

namespace Http\Controllers\Api\Game;

use App\Enums\GameType;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Tests\TestCase;

class GameListControllerTest extends TestCase
{
    private User $user;

    public function testListGames()
    {
        $res = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2, 2, 2,
                ],
            ])
            ->assertOk()
            ->json('data.game');

        $list = $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertOk()
            ->json('data.games');

        $this->assertCount(1, $list);

        $game = $list[0];
        $this->assertArrayNotHasKey('is_started', $game);
        $this->assertArrayNotHasKey('assigned_roles', $game);
        $exceptedGame = $this->game;
        unset($exceptedGame['is_started']);
        unset($exceptedGame['assigned_roles']);
        $this->assertSame(sort($exceptedGame), sort($game));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/game', [
                'data' => ['gameId' => $res['id']],
            ]);
    }

    public function testListingEmptyGames()
    {
        $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertJson([
                'data' => ['games' => []],
            ]);
    }

    public function testListingGamesByType()
    {
        $vocal = GameType::VOCAL->value;

        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2]]);
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => $vocal]);
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => $vocal]);

        $res = $this
            ->get("/api/game/list/$vocal")
            ->json('data.games');

        $fullList = $this
            ->get('/api/game/list')
            ->json('data.games');

        $this->assertCount(2, $res);
        $this->assertCount(3, $fullList);
    }

    public function testIgnoringStartedAndInvalidGames()
    {
        $res = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2, 2, 2,
                ],
            ])->json('data.game');
        Redis::set('game:1234', '');
        Redis::set('game:5678', '{}');

        Redis::set("game:{$res['id']}", array_merge(Redis::get("game:{$res['id']}"), ['is_started' => true]));

        $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertJson([
                'data' => ['games' => []],
            ]);

        Redis::del('game:1234', 'game:5678');
        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/game', [
                'gameId' => $res['id'],
            ]);
    }

    public function testListingGamesWithPrivateOnes()
    {
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => GameType::VOCAL->value]);
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => GameType::VOCAL->value | GameType::PRIVATE_GAME->value]);
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => GameType::NORMAL->value | GameType::PRIVATE_GAME->value]);

        $fullList = $this
            ->get('/api/game/list')
            ->json('data.games');

        $this->assertCount(1, $fullList);

        $vocalGames = $this
            ->get('/api/game/list')
            ->json('data.games');

        $this->assertCount(1, $vocalGames);
    }

    public function testListingOnlyPrivateGames()
    {
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => GameType::VOCAL->value]);
        $this->actingAs($this->user)->put('/api/game', ['roles' => [1, 1, 2, 2, 2], 'type' => GameType::NORMAL->value | GameType::PRIVATE_GAME->value]);

        $list = $this
            ->get('/api/game/list/' . GameType::PRIVATE_GAME->value)
            ->json('data.games');

        $this->assertCount(0, $list);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['discord_linked_at' => 'now', 'discord_id' => 1234]);

        $this->game = [
            'users' => [$this->user->id],
            'roles' => [
                1 => 2,
                2 => 3,
            ],
            'assigned_roles' => [],
            'owner' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'avatar' => $this->user->avatar,
            ],
            'is_started' => false,
            'type' => GameType::NORMAL->value,
        ];
    }
}
