<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GameVoteControllerTest extends TestCase
{
	public function testVotingUser() {
		Event::fake();

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->user->id,
			'gameId' => 'testVotingStateGame'
		])->assertStatus(Response::HTTP_NO_CONTENT);

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
		])->assertStatus(Response::HTTP_FORBIDDEN)->assertJson([
			'Wait the game to start before voting'
		]);

		Event::assertNotDispatched(GameVote::class);
	}

	public function testVotingWhileGameIsNotInVotingState() {
		Event::fake();

		$this->actingAs($this->user, 'api')->post('/api/game/vote', [
			'userId' => $this->secondUser->id,
			'gameId' => 'testStartedGame'
		])->assertStatus(Response::HTTP_FORBIDDEN)->assertJson([
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
		])->assertStatus(Response::HTTP_NO_CONTENT);

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
		$this->user = User::factory()->create();
		$this->secondUser = User::factory()->create();
		$this->thirdUser = User::factory()->makeOne([
			'id' => 3
		]);

		$this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
			'roles' => [1, 2],
			'users' => [$this->user->id, $this->secondUser->id]
		])['game'];

		Redis::set("game:testVotingStateGame", json_encode([
			'id' => 'testVotingStateGame',
			'roles' => [1, 2],
			'users' => [$this->user->id, $this->secondUser->id],
			'is_started' => true,
			'owner' => 1,
		]));

		Redis::set("game:testVotingStateGame:state", json_encode([
			'status' => 5
		]));

		Redis::set("game:testStartedGame", json_encode([
			'id' => 'testStartedGame',
			'roles' => [1, 2],
			'users' => [$this->user->id, $this->secondUser->id],
			'is_started' => true,
			'owner' => 1,
		]));

		Redis::set("game:testStartedGame:state", json_encode([
			'status' => 1
		]));
	}
}
