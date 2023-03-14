<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\GameType;
use App\Enums\Role;
use App\Enums\State;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    private User $user;

    private User $secondUser;

    private array $game;

    public function testCreatingGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')->put('/api/game', [
            'users' => [],
        ])->assertJsonValidationErrorFor('roles');
    }

    public function testCreatingGame()
    {
        $res = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ])
            ->assertJson([
                'game' => $this->game,
            ])
            ->json('game');

        $this->assertSame(
            [
                'status' => State::Waiting->value,
                'counterDuration' => State::Waiting->duration(),
                'round' => 0,
                'startTimestamp' => Carbon::now()->timestamp,
            ],
            Redis::get("game:{$res['id']}:state")
        );

        $this->game = ['id' => $res['id'], ...$this->game];

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/game', [
                'gameId' => $res['id'],
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
            ->assertOk()
            ->json('game');

        $list = $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertOk()
            ->json('games');

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
                'gameId' => $res['id'],
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
            ])->json('game');
        Redis::set('game:1234', '');
        Redis::set('game:5678', '{}');

        Redis::set("game:{$res['id']}", array_merge(Redis::get("game:{$res['id']}"), ['is_started' => true]));

        $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertExactJson([
                'games' => [],
            ]);

        Redis::del('game:1234', 'game:5678');
        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/game', [
                'gameId' => $res['id'],
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
            ])->json('game');

        $this->assertSame([
            'id' => $this->user->id,
            'username' => $this->user->username,
            'avatar' => $this->user->avatar,
        ], $game['owner']);
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
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/game')
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
            ])->json('game');

        $this->assertTrue(Redis::exists("game:{$game['id']}"));
        $this->assertTrue(Redis::exists("game:{$game['id']}:state"));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/game', [
                'gameId' => $game['id'],
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertFalse(Redis::exists("game:{$game['id']}"));
        $this->assertFalse(Redis::exists("game:{$game['id']}:state"));
        $this->assertFalse(Redis::exists("game:{$game['id']}:votes"));
        $this->assertFalse(Redis::exists("game:{$game['id']}:interactions"));
        $this->assertFalse(Redis::exists("game:{$game['id']}:deaths"));
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
            ->post('/api/game/check')
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testCheckingUnexistingGame()
    {
        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [
                'gameId' => 'unexisting',
            ])
            ->assertNotFound();
    }

    public function testCheckGame()
    {
        $game = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2,
                ],
            ])
            ->json('game');

        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [
                'gameId' => $game['id'],
            ])->assertNoContent();
    }

    public function testSettingUserGameActivityWhenCreatingGame()
    {
        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [1, 3],
            ])->json('game');

        $this->user->refresh();

        $this->assertSame($game['id'], $this->user->current_game);
    }

    public function testSettingActivityWhenJoining()
    {
        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [1, 3],
            ])->json('game');

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
                'roles' => [1, 3],
            ])->json('game');

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

    public function testRetrievingGameData()
    {
        $res = $this->actingAs($this->user, 'api')->put('/api/game', [
            'users' => [],
            'roles' => [
                Role::Werewolf->value,
                Role::WhiteWerewolf->value,
                Role::Werewolf->value,
                Role::Witch->value,
            ],
        ])->json('game');

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->get("/api/game/{$res['id']}")
            ->assertJson([
                'game' => [
                    'id' => $res['id'],
                    'owner' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'avatar' => $this->user->avatar,
                        'level' => $this->user->level,
                        'elo' => 'N/A',
                    ],
                    'roles' => [
                        Role::Werewolf->value => 2,
                        Role::WhiteWerewolf->value => 1,
                        Role::Witch->value => 1,
                    ],
                    'dead_users' => [],
                    'voted_users' => [],
                    'state' => [
                        'status' => State::Waiting->value,
                        'counterDuration' => State::Waiting->duration(),
                        'round' => 0,
                    ],
                    'current_interactions' => [],
                ],
            ]);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->get('/api/game/unexisting')
            ->assertNotFound();
    }

    public function testCreatingAGameWithMultipleSingleRoles()
    {
        $this
            ->actingAs($this->user)
            ->put('/api/game', [
                'roles' => [
                    Role::Witch->value, Role::Witch->value, Role::Werewolf->value,
                ],
            ])
            ->assertUnprocessable();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushDb();

        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();
        $this->game = [
            'users' => [$this->user->id],
            'roles' => [
                1 => 2,
                2 => 1,
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
