<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class GameVoteControllerTest extends TestCase
{
	public function testVotingUser() {
		Event::fake();

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->user->id,
			'gameId' => 'testVotingStateGame'
		])->assertStatus(204);

		Event::assertDispatched(GameVote::class);
	}

	public function testVotingWithoutBeingInGame() {
		Event::fake();

		$this->actingAs($this->thirdUser, 'api')->post('/api/game/vote', [
			'userId' => $this->user->id,
			'gameId' => $this->game['id']
		])->assertJsonValidationErrorFor('gameId');

		Event::assertNotDispatched(GameVote::class);
	}

	public function testVotingUserThatIsNotInTheGame() {
		Event::fake();

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->thirdUser->id,
			'gameId' => $this->game['id']
		])->assertJsonValidationErrorFor('userId');

		Event::assertNotDispatched(GameVote::class);
	}

	public function testVotingWhileGameIsNotStarted() {
		Event::fake();

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->secondUser->id,
			'gameId' => $this->game['id']
		])->assertStatus(403)->assertJson([
			'Wait the game to start before voting'
		]);

		Event::assertNotDispatched(GameVote::class);
	}

	public function testVotingWhileGameIsNotInVotingState() {
		Event::fake();

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->secondUser->id,
			'gameId' => 'testStartedGame'
		])->assertStatus(403)->assertJson([
			'Wait your turn to vote'
		]);

		Event::assertNotDispatched(GameVote::class);
	}

	public function testUnvoting() {
		Event::fake();

		Redis::set('game:testVotingStateGame:votes', json_encode([
			$this->user->id => [
				$this->user->id,
				$this->secondUser->id
			]
		]));

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->user->id,
			'gameId' => 'testVotingStateGame'
		])->assertStatus(204);

		Event::assertDispatched(GameUnvote::class);
		Event::assertNotDispatched(GameVote::class);

		$votes = json_decode(Redis::get('game:testVotingStateGame:votes'), true);
		$this->assertSame([
			$this->user->id => [
				$this->secondUser->id
			]
		], $votes);
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::find(1);
		$this->secondUser = User::find(2);
		$this->thirdUser = User::factory()->makeOne([
			'id' => 3
		]);

		$this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
			'roles' => [1, 2],
			'users' => [1, 2]
		])['game'];

		Redis::set("game:testVotingStateGame", json_encode([
			'id' => 'testVotingStateGame',
			'roles' => [1, 2],
			'users' => [1, 2],
			'is_started' => true,
			'owner' => 1,
			'state' => 5,
		]));

		Redis::set("game:testStartedGame", json_encode([
			'id' => 'testStartedGame',
			'roles' => [1, 2],
			'users' => [1, 2],
			'is_started' => true,
			'owner' => 1,
			'state' => 1,
		]));
	}
}
