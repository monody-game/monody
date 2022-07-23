<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\GameController;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    private GameController $controller;
    private User $user;
    private array $game;

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

		$this
			->actingAs($this->user, 'api')
			->post('/api/game/delete', [
				'game_id' => $res->json('game')['id']
			]);
    }

    public function testListGames()
    {
		$res = $this->actingAs($this->user, 'api')
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

        $this->assertCount(1, $list->json('games'));

        $game = $list->json('games')[0];
        $this->assertArrayNotHasKey('is_started', $game);
        $this->assertArrayNotHasKey('assigned_roles', $game);
        $exceptedGame = $this->game;
        unset($exceptedGame['is_started']);
        unset($exceptedGame['assigned_roles']);
        $this->assertSame(sort($exceptedGame), sort($game));

		$this
			->actingAs($this->user, 'api')
			->post('/api/game/delete', [
				'game_id' => $res->json('game')['id']
			]);
    }

	public function testListingEmptyGames() {
		$this->actingAs($this->user, 'api')
			->get('/api/game/list')
			->assertExactJson([
				'games' => []
			]);
	}

	public function testIgnoringStartedAndInvalidGames() {
		$res = $this->actingAs($this->user, 'api')
			->post('/api/game/new', [
				'users' => [],
				'roles' => [
					1, 1, 2
				],
				'is_started' => true
			]);
		Redis::set('game:1234', "");
		Redis::set('game:5678', "{}");

		$this->actingAs($this->user, 'api')
			->get('/api/game/list')
			->assertExactJson([
				'games' => []
			]);

		Redis::del('game:1234', 'game:5678');
		$this
			->actingAs($this->user, 'api')
			->post('/api/game/delete', [
				'game_id' => $res->json('game')['id']
			]);
	}

	public function testThatOwnerDoesContainsRestrictedInformations() {
		$game = $this->actingAs($this->user, 'api')
			->post('/api/game/new', [
				'users' => [],
				'roles' => [
					1, 1, 2
				],
			]);

		$this->assertSame([
			'id' => $this->user->id,
			'username' => $this->user->username,
			'avatar' => $this->user->avatar,
		], json_decode($game->getContent(), true)['game']['owner']);
	}

    public function testDeleteGameWithWrongRequest()
    {
        $this->actingAs($this->user, 'api')
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
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

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
            ])->assertStatus(Response::HTTP_NOT_FOUND);
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
            ])->assertStatus(Response::HTTP_OK);
    }

	public function testSettingUserGameActivityWhenCreatingGame() {
		$game = $this
			->actingAs($this->user, 'api')
			->post('/api/game/new', [
				'users' => [],
				'roles' => [1, 3]
			]);

		$this->user->refresh();

		$this->assertSame($game->json('game')['id'], $this->user->current_game);
	}

	public function testSettingActivityWhenJoining() {
		$secondUser = User::factory()->create();

		$game = $this
			->actingAs($this->user, 'api')
			->post('/api/game/new', [
				'users' => [],
				'roles' => [1, 3]
			]);

		$gameId = $game->json('game')['id'];

		$this
			->withoutMiddleware(RestrictToDockerNetwork::class)
			->post('/api/game/join', [
				'userId' => $secondUser->id,
				'gameId' => $gameId
			])
			->assertNoContent();

		$secondUser->refresh();

		$this->assertSame($gameId, $secondUser->current_game);
	}

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
}
