<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\States;
use App\Facades\Redis;
use App\Http\Controllers\Api\Game\GameController;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    private GameController $controller;

    private User $user;

    private User $secondUser;

    private array $game;

    public function testGeneratingGameId()
    {
        $id = $this->controller->generateGameId();
        $this->assertMatchesRegularExpression("/^[a-zA-Z\d]+[^+\/=]+$/", $id);
    }

    public function testCreatingGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')->put('/api/game', [
            'users' => [],
        ])->assertJsonValidationErrorFor('roles');
    }

    public function testCreatingGame()
    {
        $res = $this->actingAs($this->user, 'api')->put('/api/game', [
            'users' => [],
            'roles' => [
                1, 1, 2,
            ],
        ]);

        $game = Redis::get("game:{$res->json('game')['id']}");

        $this->assertSame(
            [
                'status' => States::Waiting->value,
                'counterDuration' => States::Waiting->duration(),
            ],
            Redis::get("game:{$res->json('game')['id']}:state")
        );
        $this->assertSame(sort($this->game), sort($game));

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/game', [
                'gameId' => $res->json('game')['id'],
            ]);
    }

    public function testListGames()
    {
        $res = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ])
        ->assertOk();

        $list = $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertOk();

        $this->assertCount(1, $list->json('games'));

        $game = $list->json('games')[0];
        $this->assertArrayNotHasKey('is_started', $game);
        $this->assertArrayNotHasKey('assigned_roles', $game);
        $exceptedGame = $this->game;
        unset($exceptedGame['is_started']);
        unset($exceptedGame['assigned_roles']);
        $this->assertSame(sort($exceptedGame), sort($game));

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/game', [
                'gameId' => $res->json('game')['id'],
            ]);
    }

    public function testListingEmptyGames()
    {
        $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertExactJson([
                'games' => [],
            ]);
    }

    public function testIgnoringStartedAndInvalidGames()
    {
        $res = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
                'is_started' => true,
            ]);
        Redis::set('game:1234', '');
        Redis::set('game:5678', '{}');

        $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertExactJson([
                'games' => [],
            ]);

        Redis::del('game:1234', 'game:5678');
        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/game', [
                'gameId' => $res->json('game')['id'],
            ]);
    }

    public function testThatOwnerDoesContainsRestrictedInformations()
    {
        $game = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ]);

        $this->assertSame([
            'id' => $this->user->id,
            'username' => $this->user->username,
            'avatar' => $this->user->avatar,
        ], json_decode($game->getContent(), true)['game']['owner']);
    }

    public function testDeleteGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ]);

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/game', [])
            ->assertJsonValidationErrorFor('gameId');
    }

    public function testDeletingAGame()
    {
        $game = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ]);

        $this->assertTrue(Redis::exists("game:{$game->json('game')['id']}"));
        $this->assertTrue(Redis::exists("game:{$game->json('game')['id']}:state"));

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/game', [
                'gameId' => $game->json('game')['id'],
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertFalse(Redis::exists("game:{$game->json('game')['id']}"));
        $this->assertFalse(Redis::exists("game:{$game->json('game')['id']}:state"));
        $this->assertFalse(Redis::exists("game:{$game->json('game')['id']}:votes"));
        $this->assertFalse(Redis::exists("game:{$game->json('game')['id']}:interactions"));
        $this->assertFalse(Redis::exists("game:{$game->json('game')['id']}:deaths"));
    }

    public function testCheckGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ]);

        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [])->assertJsonValidationErrorFor('gameId');
    }

    public function testCheckingUnexistingGame()
    {
        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [
                'gameId' => 'unexisting',
            ])->assertJsonValidationErrorFor('gameId');
    }

    public function testCheckGame()
    {
        $game = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ]);

        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [
                'gameId' => $game->json('game')['id'],
            ])->assertStatus(Response::HTTP_OK);
    }

    public function testSettingUserGameActivityWhenCreatingGame()
    {
        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [1, 3],
            ]);

        $this->user->refresh();

        $this->assertSame($game->json('game')['id'], $this->user->current_game);
    }

    public function testSettingActivityWhenJoining()
    {
        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [1, 3],
            ]);

        $gameId = $game->json('game')['id'];

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
                'roles' => [1, 3],
            ]);

        $gameId = $game->json('game')['id'];

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/join', [
                'userId' => $this->secondUser->id,
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $this->secondUser->refresh();

        $this->assertSame($gameId, $this->secondUser->current_game);

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/leave')
            ->assertUnprocessable();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushDb();

        $this->controller = new GameController();
        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();
        $this->game = [
            'owner' => $this->user->id,
            'users' => [$this->user->id],
            'roles' => [
                1 => 2,
                2 => 1,
            ],
            'assigned_roles' => [],
            'is_started' => false,
        ];
    }
}
