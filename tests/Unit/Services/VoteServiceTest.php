<?php

namespace Services;

use App\Events\GameKill;
use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Models\User;
use App\Services\VoteService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class VoteServiceTest extends TestCase
{
	private VoteService $service;
	private array $game;

	public function testVoting()
	{
		Event::fake();
		$gameId = $this->game['id'];
		$this->service->vote($this->secondUser->id, $this->game['id']);

		Event::assertDispatched(function (GameVote $event) use ($gameId) {
			return $event->payload === [
				'votedUser' => $this->secondUser->id,
				'gameId' => $gameId,
				'votedBy' => $this->user->id
			];
		});
		Event::assertNotDispatched(GameUnVote::class);

		$votes = json_decode(Redis::get("game:{$this->game['id']}:votes"), true);
		$this->assertSame([
			$this->secondUser->id => [$this->user->id]
		], $votes);
	}

	public function testUnvoting()
	{
		$gameId = $this->secondGame['id'];
		$votes = $this->service->vote($this->secondUser->id, $gameId);

		$this->assertSame([
			$this->secondUser->id => [$this->user->id]
		], $votes);

		Event::fakeFor(function () use ($gameId) {
			$votes = $this->service->vote($this->secondUser->id, $gameId);
			Event::assertDispatched(function (GameUnvote $event) use ($gameId) {
				return $event->payload === [
					'votedUser' => $this->secondUser->id,
					'gameId' => $gameId,
					'votedBy' => $this->user->id
				];
			});
			Event::assertNotDispatched(GameVote::class);

			$this->assertSame([], $votes);
		});
	}

	public function testKillingVotedPlayer() {
		Event::fake();
		$gameId = $this->game['id'];
		Redis::set("game:$gameId:members", json_encode(
			[
				[
					"user_id" => $this->user->id,
					"user_info" => $this->user->getAttributes()
				],
				[
					"user_id" => $this->secondUser->id,
					"user_info" => $this->secondUser->getAttributes()
				]
			]
		));
		$this->service->vote($this->user->id, $this->game['id'], $this->thirdUser->id);
		$this->service->vote($this->secondUser->id, $this->game['id']);
		$this->service->vote($this->secondUser->id, $this->game['id'], $this->secondUser->id);
		$this->service->afterVote($gameId);

		Event::assertDispatched(function (GameKill $event) use ($gameId) {
			return $event->payload === [
				'killedUser' => $this->secondUser->id,
				'gameId' => $gameId
			];
		});

		$this->assertSame(
			[
				[
					"user_id" => $this->user->id,
					"user_info" => $this->user->getAttributes()
				],
				[
					"user_id" => $this->secondUser->id,
					"user_info" => [
						...$this->secondUser->getAttributes(),
						'is_dead' => true
					]
				]
			],
			json_decode(Redis::get("game:$gameId:members"), true)
		);
	}

	public function testReturnsFalseIfThereIsNoVotes() {
		$this->assertFalse($this->service->afterVote($this->secondGame['id']));
	}

	public function testTakingRandomPlayerIfVoteEquality() {
		$gameId = $this->secondGame['id'];
		$this->service->vote($this->user->id, $gameId);
		$this->service->vote($this->secondUser->id, $gameId);

		$killedPlayer = $this->service->afterVote($gameId);

		$this->assertTrue($this->user->id === $killedPlayer || $this->secondUser->id === $killedPlayer);
	}

	public function testVotingNobody() {
		Event::fake();
		$gameId = $this->secondGame['id'];
		$this->assertFalse($this->service->afterVote($gameId));
		Event::assertDispatched(function (GameKill $event) use ($gameId) {
			return $event->payload === [
					'killedUser' => null,
					'gameId' => $gameId
				];
		});
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
				'roles' => [1, 2]
			])->getContent(), true)['game'];

		$this->secondGame = json_decode($this
			->actingAs($this->user, 'api')
			->post('/api/game/new', [
				'users' => [$this->user->id, $this->secondUser->id],
				'roles' => [1, 2]
			])->getContent(), true)['game'];
	}
}
