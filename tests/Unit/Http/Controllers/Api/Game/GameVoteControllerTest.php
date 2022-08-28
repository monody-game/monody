<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\States;
use App\Events\GameKill;
use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GameVoteControllerTest extends TestCase
{
    public function testVotingUser()
    {
        Event::fake();

        $this->actingAs($this->user, 'api')
            ->post('/api/game/vote', [
                'userId' => $this->user->id,
                'gameId' => 'testVotingStateGame',
            ])->assertNoContent();

        Event::assertDispatched(GameVote::class);
    }

    public function testVotingWithoutBeingInGame()
    {
        Event::fake();

        $this->actingAs($this->thirdUser, 'api')->post('/api/game/vote', [
            'userId' => $this->user->id,
            'gameId' => $this->game['id'],
        ])->assertJsonValidationErrorFor('gameId');

        Event::assertNotDispatched(GameVote::class);
    }

    public function testVotingUserThatIsNotInTheGame()
    {
        Event::fake();

        $this->actingAs($this->user, 'api')->post('/api/game/vote', [
            'userId' => $this->thirdUser->id,
            'gameId' => $this->game['id'],
        ])->assertJsonValidationErrorFor('userId');

        Event::assertNotDispatched(GameVote::class);
    }

    public function testVotingWhileGameIsNotStarted()
    {
        Event::fake();

        $this->actingAs($this->user, 'api')->post('/api/game/vote', [
            'userId' => $this->secondUser->id,
            'gameId' => $this->game['id'],
        ])
            ->assertForbidden()
            ->assertJson([
                'Wait the game to start before voting',
            ]);

        Event::assertNotDispatched(GameVote::class);
    }

    public function testVotingWhileGameIsNotInVotingState()
    {
        Event::fake();

        $this->actingAs($this->user, 'api')->post('/api/game/vote', [
            'userId' => $this->secondUser->id,
            'gameId' => 'testStartedGame',
        ])->assertForbidden()->assertJson([
            'Wait your turn to vote',
        ]);

        Event::assertNotDispatched(GameVote::class);
    }

    public function testUnvoting()
    {
        Event::fake();

        Redis::set('game:testVotingStateGame:votes', [
            $this->user->id => [
                $this->user->id,
                $this->secondUser->id,
            ],
        ]);

        $this->actingAs($this->user, 'api')->post('/api/game/vote', [
            'userId' => $this->user->id,
            'gameId' => 'testVotingStateGame',
        ])->assertNoContent();

        Event::assertDispatched(GameUnvote::class);
        Event::assertNotDispatched(GameVote::class);

        $votes = Redis::get('game:testVotingStateGame:votes');
        $this->assertSame([
            $this->user->id => [
                $this->secondUser->id,
            ],
        ], $votes);
    }

    public function testTriggeringAfterVote()
    {
        Event::fake();

        Redis::set('game:testVotingStateGame:votes', '');

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/game/vote', [
                'userId' => $this->user->id,
                'gameId' => 'testVotingStateGame',
            ]);

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/aftervote', [
                'gameId' => 'testVotingStateGame',
            ])->assertNoContent();

        Event::assertDispatched(function (GameKill $event) {
            return $event->payload === [
                'killedUser' => $this->user->id,
                'gameId' => 'testVotingStateGame',
                'context' => 'vote',
            ];
        });
    }

    public function testTriggeringAfterVoteWithUnStartedGame()
    {
        Redis::set('game:testVotingStateGame:votes', '');

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/aftervote', [
                'gameId' => 'testVotingStateGame',
            ])
            ->assertOk()
            ->assertJson([
                'Not any player to vote, or vote cancelled',
            ]);
    }

    public function testTriggeringAfterVoteWithoutAnyVote()
    {
        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/aftervote', [
                'gameId' => $this->game['id'],
            ])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'Game is not started',
            ]);
    }

    public function testVotingDeadPlayer()
    {
        Redis::set('game:testVotingStateGame:members', [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        Event::fake();

        Redis::set('game:testVotingStateGame:votes', [
            $this->user->id => [
                $this->secondUser->id,
            ],
        ]);

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/aftervote', [
                'gameId' => 'testVotingStateGame',
            ])
            ->assertNoContent();

        Event::assertDispatched(function (GameKill $event) {
            return $event->payload === [
                'killedUser' => $this->user->id,
                'gameId' => 'testVotingStateGame',
                'context' => 'vote',
            ];
        });

        Event::fakeFor(function () {
            $this
                ->actingAs($this->user, 'api')
                ->post('/api/game/vote', [
                    'userId' => $this->user->id,
                    'gameId' => 'testVotingStateGame',
                ])
                ->assertUnprocessable();

            Event::assertNotDispatched(GameKill::class);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();
        $this->thirdUser = User::factory()->makeOne([
            'id' => 3,
        ]);

        $this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'roles' => [1, 2],
            'users' => [$this->user->id, $this->secondUser->id],
        ])['game'];

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        Redis::set('game:testVotingStateGame', [
            'id' => 'testVotingStateGame',
            'roles' => [1, 2],
            'users' => [$this->user->id, $this->secondUser->id],
            'is_started' => true,
            'owner' => 1,
        ]);

        Redis::set('game:testVotingStateGame:members', [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        Redis::set('game:testVotingStateGame:state', [
            'status' => States::Vote,
        ]);

        Redis::set('game:testStartedGame', [
            'id' => 'testStartedGame',
            'roles' => [1, 2],
            'users' => [$this->user->id, $this->secondUser->id],
            'is_started' => true,
            'owner' => 1,
        ]);

        Redis::set('game:testStartedGame:members', [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        Redis::set('game:testStartedGame:state', [
            'status' => States::Starting,
        ]);
    }
}
