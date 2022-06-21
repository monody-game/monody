<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Models\User;
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
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'roles' => [1, 2],
            'users' => []
        ])['game'];
    }
}
