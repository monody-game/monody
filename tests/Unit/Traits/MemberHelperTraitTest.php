<?php

namespace Tests\Unit\Traits;

use App\Enums\Role;
use App\Enums\Team;
use App\Facades\Redis;
use App\Models\User;
use App\Traits\MemberHelperTrait;
use Exception;
use Tests\TestCase;

class MemberHelperTraitTest extends TestCase
{
    use MemberHelperTrait;

    private array $game;

    private string $gameId = 'id';

    private string $key;

    private array $user;

    private array $secondUser;

    public function testGettingMembers()
    {
        $this->assertSame(Redis::get($this->key), $this->getMembers($this->gameId));
    }

    public function testGettingSpecificMember()
    {
        $this->assertSame(['user_id' => $this->user['id'], 'user_info' => $this->user], $this->getMember($this->user['id'], $this->gameId));
    }

    public function testGettingDoubledUser()
    {
        $this->expectException(Exception::class);
        $this->getMember($this->user['id'], $this->gameId . '1');
    }

    public function testHasMember()
    {
        $this->assertTrue($this->hasMember($this->user['id'], $this->gameId));
        $this->assertFalse($this->hasMember('inexistantuserid', $this->gameId));
    }

    public function testGettingMemberOnInexistantKey()
    {
        $this->assertEmpty($this->getMembers('unexistantgame'));
        $this->assertFalse($this->getMember($this->user['id'], 'unexistantgame'));
        $this->assertFalse($this->hasMember($this->user['id'], 'unexistantgame'));
    }

    public function testGettingWerewolves()
    {
        $this->assertSame([
            $this->user['id'],
            $this->secondUser['id'],
        ], $this->getUsersByTeam(Team::Werewolves, $this->game['id']));
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

        $this->secondUser = [
            'id' => $secondUser->id,
            'username' => $secondUser->username,
            'avatar' => $secondUser->avatar,
            'level' => $secondUser->level,
        ];

        $this->key = "game:$this->gameId:members";

        Redis::set($this->key, [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        Redis::set("game:{$this->gameId}1:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
        ]);

        $this->game = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [Role::InfectedWerewolf->value, Role::Werewolf->value],
                'users' => [$this->secondUser['id']],
            ])
            ->json('data.game');

        Redis::set("game:{$this->game['id']}", array_merge($this->game, [
            'assigned_roles' => [
                $this->user['id'] => Role::InfectedWerewolf->value,
                $this->secondUser['id'] => Role::Werewolf->value,
            ],
        ]));

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);
    }
}
