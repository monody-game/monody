<?php

namespace Tests\Unit\Traits;

use App\Models\User;
use App\Traits\MemberHelperTrait;
use Illuminate\Support\Facades\Redis;
use Exception;
use Tests\TestCase;

class MemberHelperTraitTest extends TestCase
{
	use MemberHelperTrait;

	private string $gameId = 'id';
	private string $key;
	private array $user;

	public function testGettingMembers() {
		$this->assertSame(json_decode(Redis::get($this->key), true), $this->getMembers($this->gameId));
	}

	public function testGettingSpecificMember() {
		$this->assertSame(['user_id' => $this->user['id'], 'user_info' => $this->user], $this->getMember($this->user['id'], $this->gameId));
	}

	public function testGettingDoubledUser() {
		$this->expectException(Exception::class);
		$this->getMember($this->user['id'], $this->gameId . '1');
	}

	public function testHasMember() {
		$this->assertTrue($this->hasMember($this->user['id'], $this->gameId));
		$this->assertFalse($this->hasMember('ioauouihf135', $this->gameId));
	}

	public function testGettingMemberOnInexistantKey() {
		$this->assertFalse($this->getMember($this->user['id'], '14U4Xohuxt'));
	}

	protected function setUp(): void
	{
		parent::setUp();

		[$user, $secondUser] = User::factory()->count(2)->create();

		$this->user = [
			'id' => $user->id,
			'username' => $user->username,
			'avatar' => $user->avatar,
			'level' => $user->level,
		];

		$secondUser = [
			'id' => $secondUser->id,
			'username' => $secondUser->username,
			'avatar' => $secondUser->avatar,
			'level' => $secondUser->level,
		];

		$this->key = "game:$this->gameId:members";

		Redis::set($this->key, json_encode([
			['user_id' => $this->user['id'], 'user_info' => $this->user],
			['user_id' => $secondUser['id'], 'user_info' => $secondUser],
		]));

		Redis::set("game:{$this->gameId}1:members", json_encode([
			['user_id' => $this->user['id'], 'user_info' => $this->user],
			['user_id' => $this->user['id'], 'user_info' => $this->user],
		]));
	}
}
