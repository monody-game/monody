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

			$this->assertSame([
				$this->secondUser->id => []
			], $votes);
		});
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->service = new VoteService();

		$this->user = User::factory()->create();
		$this->secondUser = User::factory()->create();

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
