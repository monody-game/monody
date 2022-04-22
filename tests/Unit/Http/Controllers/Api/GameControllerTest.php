<?php

namespace Http\Controllers\Api;

use App\Http\Controllers\Api\GameController;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    private GameController $controller;
    private User $user;
    private array $game;


    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new GameController();
        $this->user = User::factory()->create();
        $this->game = [
            'owner' => $this->user->id,
            'users' => [$this->user->id],
            'roles' => [
                1 => 2,
                2 => 1,
            ],
            'assigned_roles' => [],
            'is_started' => false,
        ];
    }

    public function testGeneratingGameId()
    {
        $id = $this->controller->generateGameId();
        $this->assertMatchesRegularExpression("/^[a-zA-Z\d]+[^+\/=]+$/", $id);
    }

    public function testCreatingGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'users' => [],
        ])->assertJsonValidationErrorFor('roles');
    }

    public function testCreatingGame()
    {
        $res = $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'users' => [],
            'roles' => [
                1, 1, 2
            ],
        ]);

        $game = Redis::get("game:{$res->json('game')['id']}");
        $this->assertNotNull(Redis::get("game:{$res->json('game')['id']}:state"));
        $this->assertJson($game);
        $game = json_decode($game, true);

        $this->assertSame(sort($this->game), sort($game));
    }

    public function testListGames()
    {
        $this->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [],
                'roles' => [
                    1, 1, 2
                ],
            ]);

        $list = $this->actingAs($this->user, 'api')
            ->get('/api/game/list')
            ->assertJsonStructure([
                'games' => []
            ]);

        $this->assertCount(2, $list->json('games'));

        $game = $list->json('games')[0];
        $this->assertSame(sort($this->game), sort($game));
    }

    public function testDeleteGameWithWrongRequest()
    {
        $game = $this->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [],
                'roles' => [
                    1, 1, 2
                ],
            ]);

        $this->actingAs($this->user, 'api')
            ->post('/api/game/delete', [])->assertJsonValidationErrorFor('game_id');
    }

    public function testDeleteGame()
    {
        $game = $this->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [],
                'roles' => [
                    1, 1, 2
                ],
            ]);

        $this->actingAs($this->user, 'api')
            ->post('/api/game/delete', [
                'game_id' => $game->json('game')['id']
            ]);

        $this->assertNull(Redis::get("game:{$game->json('game')['id']}"));
        $this->assertNull(Redis::get("game:{$game->json('game')['id']}:state"));
    }

    public function testCheckGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [],
                'roles' => [
                    1, 1, 2
                ],
            ]);

        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [])->assertJsonValidationErrorFor('game_id');
    }

    public function testCheckingUnexistingGame()
    {
        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [
                'game_id' => 'unexisting'
            ])->assertStatus(404);
    }

    public function testCheckGame()
    {
        $game = $this->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'users' => [],
                'roles' => [
                    1, 1, 2
                ],
            ]);

        $this->actingAs($this->user, 'api')
            ->post('/api/game/check', [
                'game_id' => $game->json('game')['id']
            ])->assertStatus(200);
    }
}
