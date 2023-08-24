<?php

namespace Http\Controllers\Api\Game;

use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Tests\TestCase;

class JoinGameControllerTest extends TestCase
{
    private User $user;

    private User $secondUser;

    public function testSettingActivityWhenJoining()
    {
        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [1, 3, 2, 2, 2],
            ])->json('data.game');

        $gameId = $game['id'];

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/join', [
                'userId' => $this->secondUser->id,
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $this->secondUser->refresh();

        $this->assertSame($gameId, $this->secondUser->current_game);
    }

    public function testRemovingActivityWhenLeaving()
    {
        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [1, 3, 2, 2, 2],
            ])->json('data.game');

        $gameId = $game['id'];

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/join', [
                'userId' => $this->secondUser->id,
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $this->secondUser->refresh();

        $this->assertSame($gameId, $this->secondUser->current_game);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/leave', [
                'userId' => $this->secondUser->id,
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $this->secondUser->refresh();

        $this->assertSame(null, $this->secondUser->current_game);
    }

    public function testLeavingGameWithoutGivingUserId()
    {
        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/leave')
            ->assertUnprocessable();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['discord_linked_at' => 'now', 'discord_id' => 1234]);
        $this->secondUser = User::factory()->create();
    }
}
