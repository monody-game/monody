<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Team;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $game;

    public function testGetAllRoles(): void
    {
        $response = $this->getJson('/api/roles')->json('data.roles');

        $this->assertTrue(count($response) > 1);
    }

    public function testGettingAllRolesForOneGame(): void
    {
        $this
            ->getJson("/api/roles/game/{$this->game['id']}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'roles' => [
                        Role::Werewolf->full(),
                        Role::SimpleVillager->full(),
                        Role::Psychic->full(),
                    ],
                ],
            ]);
    }

    public function testGetOneRole(): void
    {
        $this->getJson('/api/roles/get/1')
            ->assertJson([
                'data' => [
                    'role' => [
                        'id' => Role::Werewolf->value,
                        'name' => 'werewolf',
                        'display_name' => 'Loup-garou',
                        'limit' => -1,
                        'weight' => Role::Werewolf->weight(),
                        'description' => str_replace('*', '', Role::Werewolf->describe()),
                        'team' => [
                            'id' => Team::Werewolves->value,
                            'name' => Team::Werewolves->name(),
                            'display_name' => Team::Werewolves->stringify(),
                        ],
                    ],
                ],
            ]);
    }

    public function testGettingOneRoleWithMarkdown(): void
    {
        $this->getJson('/api/roles/get/1?markdown=true')
            ->assertJson([
                'data' => [
                    'role' => [
                        'id' => Role::Werewolf->value,
                        'name' => 'werewolf',
                        'display_name' => 'Loup-garou',
                        'limit' => -1,
                        'weight' => Role::Werewolf->weight(),
                        'description' => Role::Werewolf->describe(),
                        'team' => [
                            'id' => Team::Werewolves->value,
                            'name' => Team::Werewolves->name(),
                            'display_name' => Team::Werewolves->stringify(),
                        ],
                    ],
                ],
            ]);
    }

    public function testGetUnexistentRole(): void
    {
        $response = $this->getJson('/api/roles/get/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGettingWerewolvesRoles()
    {
        $this
            ->getJson('/api/roles/2')
            ->assertJson([
                'data' => ['roles' => array_map(fn (Role $role) => $role->value, Team::Werewolves->roles())],
            ])
            ->assertStatus(Response::HTTP_OK);
    }

    public function testGettingVillagersRoles()
    {
        $this
            ->getJson('/api/roles/1')
            ->assertJson([
                'data' => ['roles' => array_map(fn (Role $role) => $role->value, Team::Villagers->roles())],
            ])
            ->assertStatus(Response::HTTP_OK);
    }

    public function testAssigningRoles()
    {
        $assigned = Redis::get("game:{$this->game['id']}")['assigned_roles'];
        $this->assertEmpty($assigned);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/roles/assign', ['gameId' => $this->game['id']])
            ->assertNoContent();

        $game = Redis::get("game:{$this->game['id']}");

        $this->assertCount(5, $game['assigned_roles']);
        $this->assertCount(1, $game['werewolves']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$user, $secondUser, $third, $fourth, $fifth] = User::factory(5)->make();

        $this->game = $this
            ->actingAs($user, 'api')
            ->put('/api/game', ['roles' => [1, 2, 2, 2, 3]])
            ->json('data.game');

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $user['id'], 'user_info' => $user],
            ['user_id' => $secondUser['id'], 'user_info' => $secondUser],
            ['user_id' => $third['id'], 'user_info' => $third],
            ['user_id' => $fourth['id'], 'user_info' => $fourth],
            ['user_id' => $fifth['id'], 'user_info' => $fifth],
        ]);
    }
}
