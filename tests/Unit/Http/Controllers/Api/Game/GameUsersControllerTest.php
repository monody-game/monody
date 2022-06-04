<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Models\User;
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
            ->call('GET', '/api/game/users', ['game_id' => $this->game['id']])
            ->assertJson([
                'users' => [
                    $this->user['id']
                ]
            ]);
    }

    public function testListingUnexistingGameUsers()
    {
        $this->actingAs($this->user, 'api')
            ->call('GET', '/api/game/users', ['game_id' => 'unexisting id, obviously'])
            ->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);

        $this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'roles' => [1, 2],
            'users' => []
        ])['game'];
    }
}
