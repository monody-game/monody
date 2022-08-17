<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GameUsersControllerTest extends TestCase
{
    public function testListUsersWithoutGameId()
    {
        $this->actingAs($this->user, 'api')->get('/api/game/users')->assertStatus(400);
    }

    public function testListUsers()
    {
        $this->actingAs($this->user, 'api')
            ->call('GET', '/api/game/users', ['gameId' => $this->game['id']])
            ->assertJson([
                'users' => [
                    $this->user['id'],
                ],
            ]);
    }

    public function testListingUnexistingGameUsers()
    {
        $this->actingAs($this->user, 'api')
            ->call('GET', '/api/game/users', ['gameId' => 'unexisting id, obviously'])
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGettingUserRole()
    {
        $this->secondUser->current_game = 'id';

        Redis::set('game:id', json_encode([
            'assigned_roles' => [$this->secondUser->id => 1],
        ]));

        $response = $this->actingAs($this->secondUser, 'api')
            ->call('GET', "/api/game/user/{$this->secondUser->id}/role", ['gameId' => 'id'])
            ->assertOk()
            ->json();

        $this->assertSame(Role::find(1)->getOriginal(), $response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->secondUser] = User::factory(2)->create();

        $this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'roles' => [1, 2],
            'users' => [],
        ])['game'];
    }
}
