<?php

namespace Tests\Unit\Services;

use App\Enums\States;
use App\Events\GameKill;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use App\Services\VoteService;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VoteServiceTest extends TestCase
{
    private VoteService $service;

    private array $game;

    private array $secondGame;

    private array $thirdGame;

    private User $user;

    private User $secondUser;

    private User $thirdUser;

    private User $fourthUser;

    public function testVotingUser()
    {
        $this->service->vote($this->secondUser->id, $this->game['id']);

        $votes = Redis::get("game:{$this->game['id']}:votes");
        $this->assertSame([
            $this->secondUser->id => [$this->user->id],
        ], $votes);

        $this->service->vote($this->user->id, $this->game['id'], $this->secondUser->id);

        $votes = Redis::get("game:{$this->game['id']}:votes");

        $this->assertSame([
            $this->secondUser->id => [$this->user->id],
            $this->user->id => [$this->secondUser->id],
        ], $votes);
    }

    public function testSwitchingVote()
    {
        Redis::set("game:{$this->game['id']}:votes", []);
        $this->service->vote($this->secondUser->id, $this->game['id']);

        $votes = Redis::get("game:{$this->game['id']}:votes");
        $this->assertSame([
            $this->secondUser->id => [$this->user->id],
        ], $votes);

        $this->service->vote($this->user->id, $this->game['id']);

        $votes = Redis::get("game:{$this->game['id']}:votes");

        $this->assertSame([
            $this->user->id => [$this->user->id],
        ], $votes);
    }

    public function testUnvoting()
    {
        $gameId = $this->secondGame['id'];
        $votes = $this->service->vote($this->secondUser->id, $gameId);

        $this->assertSame([
            $this->secondUser->id => [$this->user->id],
        ], $votes);

        $votes = $this->service->vote($this->secondUser->id, $gameId);

        $this->assertSame([], $votes);
    }

    public function testVotingAndUnvotingRepetively()
    {
        $gameId = $this->secondGame['id'];
        $this->service->vote($this->secondUser->id, $gameId, $this->user->id);
        $votes = $this->service->vote($this->secondUser->id, $gameId, $this->secondUser->id);

        $this->assertSame([
            $this->secondUser->id => [$this->user->id, $this->secondUser->id],
        ], $votes);

        $votes = $this->service->vote($this->secondUser->id, $gameId, $this->user->id);

        $this->assertSame([$this->secondUser->id => [$this->secondUser->id]], $votes);

        $votes = $this->service->vote($this->secondUser->id, $gameId, $this->user->id);

        $this->assertSame([
            $this->secondUser->id => [$this->secondUser->id, $this->user->id],
        ], $votes);

        $votes = $this->service->vote($this->secondUser->id, $gameId, $this->user->id);

        $this->assertSame([$this->secondUser->id => [$this->secondUser->id]], $votes);
    }

    public function testKillingVotedPlayer()
    {
        Event::fake();
        $gameId = $this->game['id'];
        Redis::set("game:$gameId:members", json_encode(
            [
                [
                    'user_id' => $this->user->id,
                    'user_info' => $this->user->getAttributes(),
                ],
                [
                    'user_id' => $this->secondUser->id,
                    'user_info' => $this->secondUser->getAttributes(),
                ],
            ]
        ));
        $this->service->vote($this->user->id, $this->game['id'], $this->thirdUser->id);
        $this->service->vote($this->secondUser->id, $this->game['id']);
        $this->service->vote($this->secondUser->id, $this->game['id'], $this->secondUser->id);
        $this->service->afterVote($gameId);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/message/deaths', ['gameId' => $this->game['id']]);

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => $this->secondUser->id,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });

        $this->assertSame(
            [
                [
                    'user_id' => $this->user->id,
                    'user_info' => $this->user->getAttributes(),
                ],
                [
                    'user_id' => $this->secondUser->id,
                    'user_info' => [
                        ...$this->secondUser->getAttributes(),
                        'is_dead' => true,
                    ],
                ],
            ],
            Redis::get("game:$gameId:members")
        );
    }

    public function testReturnsFalseIfThereIsNoVotes()
    {
        $this->assertFalse($this->service->afterVote($this->secondGame['id']));
    }

    public function testTakingRandomPlayerIfVoteEquality()
    {
        $gameId = $this->secondGame['id'];
        $this->service->vote($this->user->id, $gameId);
        $this->service->vote($this->secondUser->id, $gameId);

        $killedPlayer = $this->service->afterVote($gameId);

        $this->assertTrue($this->user->id === $killedPlayer || $this->secondUser->id === $killedPlayer);
    }

    public function testVotingNobody()
    {
        Event::fake();
        $gameId = $this->secondGame['id'];
        $this->assertFalse($this->service->afterVote($gameId));

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });
    }

    public function testVotingDeadPlayerInService()
    {
        $gameId = $this->secondGame['id'];

        Event::fake();
        $this->service->vote($this->user->id, $gameId);
        $this->assertSame($this->user->id, $this->service->afterVote($gameId));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/message/deaths', ['gameId' => $gameId]);

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => $this->user->id,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });

        $this->assertSame([], $this->service->vote($this->user->id, $gameId));
        $this->assertFalse($this->service->afterVote($gameId));

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });
    }

    public function testKillingInexistantMember()
    {
        $this->assertFalse($this->service->kill('inexistantUser', $this->secondGame['id'], 'vote'));
    }

    public function testAfterVotingDeadMember()
    {
        $gameId = $this->secondGame['id'];
        Event::fake();

        $this->service->kill($this->secondUser->id, $gameId, 'vote');
        Redis::set("game:$gameId:votes", json_encode([
            $this->secondUser->id => [
                $this->secondUser->id,
            ],
        ]));

        $this->assertFalse($this->service->afterVote($gameId));

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });
    }

    public function testCheckingIfMajorityHasVoted()
    {
        $gameId = $this->thirdGame['id'];

        $this->service->vote($this->user->id, $gameId, $this->user->id);
        $this->assertFalse(VoteService::hasMajorityVoted($this->thirdGame));

        $this->service->vote($this->user->id, $gameId, $this->secondUser->id);
        $this->assertFalse(VoteService::hasMajorityVoted($this->thirdGame));

        $this->service->vote($this->fourthUser->id, $gameId, $this->thirdUser->id);
        $this->assertTrue(VoteService::hasMajorityVoted($this->thirdGame));

        $this->service->vote($this->thirdUser->id, $gameId, $this->fourthUser->id);
        $this->assertTrue(VoteService::hasMajorityVoted($this->thirdGame));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VoteService();

        [$this->user, $this->secondUser, $this->thirdUser, $this->fourthUser] = User::factory(4)->create();

        $this->game = json_decode($this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [$this->user->id, $this->secondUser->id],
                'roles' => [1, 2],
            ])->getContent(), true)['game'];

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        Redis::set("game:{$this->game['id']}:state", [
            'status' => States::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => States::Vote->duration(),
        ]);

        $this->secondGame = json_decode($this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [$this->user->id, $this->secondUser->id],
                'roles' => [1, 2],
            ])->getContent(), true)['game'];

        Redis::set("game:{$this->secondGame['id']}:members", [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        Redis::set("game:{$this->secondGame['id']}:state", [
            'status' => States::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => States::Vote->duration(),
        ]);

        $this->thirdGame = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'users' => [$this->secondUser->id, $this->thirdUser->id, $this->fourthUser->id],
                'roles' => [1, 2],
            ])->json('game');

        Redis::set("game:{$this->thirdGame['id']}:members", [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
            ['user_id' => $this->thirdUser->id, 'user_info' => $this->thirdUser],
            ['user_id' => $this->fourthUser->id, 'user_info' => $this->fourthUser],
        ]);

        Redis::set("game:{$this->thirdGame['id']}:state", [
            'status' => States::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => States::Vote->duration(),
        ]);
    }
}
