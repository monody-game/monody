<?php

namespace Tests\Unit;

use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Models\User;
use App\VoteService;
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
		$this->service->vote(2, $this->game['id']);

		Event::assertDispatched(function (GameVote $event) use ($gameId) {
			return $event->payload === [
					'votedUser' => 2,
					'gameId' => $gameId,
					'votedBy' => 1
				];
		});
		Event::assertNotDispatched(GameUnVote::class);

		$votes = json_decode(Redis::get("game:{$this->game['id']}:votes"), true);
		$this->assertSame([
			2 => [1]
		], $votes);
	}

	public function testUnvoting()
	{
		$gameId = $this->secondGame['id'];
		$votes = $this->service->vote(2, $gameId);

		$this->assertSame([
			2 => [1]
		], $votes);

		Event::fakeFor(function () use ($gameId) {
			$votes = $this->service->vote(2, $gameId);
			Event::assertDispatched(function (GameUnvote $event) use ($gameId) {
				return $event->payload === [
						'votedUser' => 2,
						'gameId' => $gameId,
						'votedBy' => 1
					];
			});
			Event::assertNotDispatched(GameVote::class);

			$this->assertSame([
				2 => []
			], $votes);
		});
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->service = new VoteService();

		$user = User::factory()->makeOne([
			'id' => 1
		]);
		User::factory()->makeOne([
			'id' => 2
		]);

		$this->game = json_decode($this
			->actingAs($user, 'api')
			->post('/api/game/new', [
				'users' => [1, 2],
				'roles' => [1, 2]
			])->getContent(), true)['game'];
		$this->secondGame = json_decode($this
			->actingAs($user, 'api')
			->post('/api/game/new', [
				'users' => [1, 2],
				'roles' => [1, 2]
			])->getContent(), true)['game'];
	}
}
