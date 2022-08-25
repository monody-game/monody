<?php

namespace Tests\Unit\Services;

use App\Events\GameKill;
use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Facades\Redis;
use App\Models\User;
use App\Services\VoteService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VoteServiceTest extends TestCase
{
    private VoteService $service;

    private array $game;

    private array $secondGame;

    private User $user;

    private User $secondUser;

    private User $thirdUser;

    public function testVoting()
    {
        Event::fake();
        $gameId = $this->game['id'];
        $this->service->vote($this->secondUser->id, $this->game['id']);

        Event::assertDispatched(function (GameVote $event) use ($gameId) {
            return $event->payload === [
                'votedUser' => $this->secondUser->id,
                'gameId' => $gameId,
                'votedBy' => $this->user->id,
            ];
        });
        Event::assertNotDispatched(GameUnVote::class);

        $votes = Redis::get("game:{$this->game['id']}:votes");
        $this->assertSame([
            $this->secondUser->id => [$this->user->id],
        ], $votes);
    }

    public function testUnvoting()
    {
        $gameId = $this->secondGame['id'];
        $votes = $this->service->vote($this->secondUser->id, $gameId);

        $this->assertSame([
            $this->secondUser->id => [$this->user->id],
        ], $votes);

        Event::fakeFor(function () use ($gameId) {
            $votes = $this->service->vote($this->secondUser->id, $gameId);
            Event::assertDispatched(function (GameUnvote $event) use ($gameId) {
                return $event->payload === [
                    'votedUser' => $this->secondUser->id,
                    'gameId' => $gameId,
                    'votedBy' => $this->user->id,
                ];
            });
            Event::assertNotDispatched(GameVote::class);

            $this->assertSame([], $votes);
        });
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
        $this->service->afterVote($gameId, 'vote');

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
        $this->assertFalse($this->service->afterVote($this->secondGame['id'], 'vote'));
    }

    public function testTakingRandomPlayerIfVoteEquality()
    {
        $gameId = $this->secondGame['id'];
        $this->service->vote($this->user->id, $gameId);
        $this->service->vote($this->secondUser->id, $gameId);

        $killedPlayer = $this->service->afterVote($gameId, 'vote');

        $this->assertTrue($this->user->id === $killedPlayer || $this->secondUser->id === $killedPlayer);
    }

    public function testVotingNobody()
    {
        Event::fake();
        $gameId = $this->secondGame['id'];
        $this->assertFalse($this->service->afterVote($gameId, 'vote'));

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
        $this->assertSame($this->user->id, $this->service->afterVote($gameId, 'vote'));

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => $this->user->id,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });

        $this->assertSame([], $this->service->vote($this->user->id, $gameId));
        $this->assertFalse($this->service->afterVote($gameId, 'vote'));

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
        $this->assertFalse($this->service->kill('inexistantUser', $this->secondGame['id']));
    }

    public function testAfterVotingDeadMember()
    {
        $gameId = $this->secondGame['id'];
        Event::fake();

        $this->service->kill($this->secondUser->id, $gameId);
        Redis::set("game:$gameId:votes", json_encode([
            $this->secondUser->id => [
                $this->secondUser->id,
            ],
        ]));

        $this->assertFalse($this->service->afterVote($gameId, 'vote'));

        Event::assertDispatched(function (GameKill $event) use ($gameId) {
            return $event->payload === [
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => 'vote',
            ];
        });
    }

    public function testSwitchingVote()
    {
        Event::fake();
        $gameId = $this->game['id'];
        $this->service->vote($this->secondUser->id, $this->game['id']);

        Event::assertDispatched(function (GameVote $event) use ($gameId) {
            return $event->payload === [
                'votedUser' => $this->secondUser->id,
                'gameId' => $gameId,
                'votedBy' => $this->user->id,
            ];
        });

        $this->service->vote($this->user->id, $this->game['id']);

        Event::assertDispatched(function (GameUnvote $event) use ($gameId) {
            return $event->payload === [
                'votedUser' => $this->secondUser->id,
                'gameId' => $gameId,
                'votedBy' => $this->user->id,
            ];
        });

        Event::assertDispatched(function (GameVote $event) use ($gameId) {
            return $event->payload === [
                'votedUser' => $this->user->id,
                'gameId' => $gameId,
                'votedBy' => $this->user->id,
            ];
        });

        $votes = Redis::get("game:{$this->game['id']}:votes");
        $this->assertSame([
            $this->user->id => [$this->user->id],
        ], $votes);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VoteService();

        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();
        $this->thirdUser = User::factory()->create();

        $this->game = json_decode($this
            ->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [$this->user->id, $this->secondUser->id],
                'roles' => [1, 2],
            ])->getContent(), true)['game'];

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);

        $this->secondGame = json_decode($this
            ->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [$this->user->id, $this->secondUser->id],
                'roles' => [1, 2],
            ])->getContent(), true)['game'];

        Redis::set("game:{$this->secondGame['id']}:members", [
            ['user_id' => $this->user->id, 'user_info' => $this->user],
            ['user_id' => $this->secondUser->id, 'user_info' => $this->secondUser],
        ]);
    }
}
