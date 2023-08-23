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
                    1, 1, 2, 2, 2,
                ],
            ])
            ->assertJson(['data' => ['game' => $this->game]])
            ->json('data.game');

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

    public function testThatOwnerDoesContainsRestrictedInformations()
    {
        $game = $this->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [],
                'roles' => [
                    1, 1, 2, 2, 2,
                ],
            ])->json('data.game');

        $this->assertSame([
            'id' => $this->user->id,
            'username' => $this->user->username,
            'avatar' => $this->user->avatar,
        ], $game['owner']);
    }

    public function testDeleteGameWithWrongRequest()
    {
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
                    1, 1, 2, 2, 2,
                ],
            ])->json('data.game');

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
            ->post('/api/game/check')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
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
                    1, 1, 2, 2, 2,
                ],
            ])
            ->json('data.game');

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
                'roles' => [1, 3, 2, 2, 2],
            ])->json('data.game');

        $this->user->refresh();

        $this->assertSame($game['id'], $this->user->current_game);
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
                Role::SimpleVillager->value,
            ],
        ])->json('data.game');

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->get("/api/game/data/{$res['id']}/{$this->user->id}")
            ->assertJson([
                'data' => [
                    'game' => [
                        'id' => $res['id'],
                        'owner' => [
                            'id' => $this->user->id,
                            'username' => $this->user->username,
                            'avatar' => $this->user->avatar,
                            'level' => $this->user->level,
                        ],
                        'roles' => [
                            Role::Werewolf->value => 2,
                            Role::WhiteWerewolf->value => 1,
                            Role::Witch->value => 1,
                            Role::SimpleVillager->value => 1,
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
                ],
            ]);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->get('/api/game/unexisting/test')
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
