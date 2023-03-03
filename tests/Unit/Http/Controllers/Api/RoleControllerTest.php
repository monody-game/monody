<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use App\Traits\InteractsWithRedis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase, InteractsWithRedis;

    private User $user;

    private User $secondUser;

    private array $game;

    public function testGetAllRoles(): void
    {
        $response = $this->getJson('/api/roles')->json('roles');

        $this->assertTrue(count($response) > 1);
    }

    public function testGettingAllRolesForOneGame(): void
    {
        $this
            ->getJson("/api/roles/game/{$this->game['id']}")
            ->assertOk()
            ->assertJson([
                Roles::Werewolf->full(),
                Roles::SimpleVillager->full(),
            ]);
    }

    public function testGetOneRole(): void
    {
        $response = $this->getJson('/api/roles/get/1');
        $response->assertJson([
            'role' => [
                'id' => 1,
                'name' => 'werewolf',
                'display_name' => 'Loup-garou',
                'limit' => null,
                'weight' => 2,
                'team' => [
                    'id' => Teams::Werewolves->value,
                    'name' => Teams::Werewolves->name(),
                    'display_name' => Teams::Werewolves->stringify(),
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
                'roles' => [1],
            ])
            ->assertStatus(Response::HTTP_OK);
    }

    public function testGettingVillagersRoles()
    {
        $this
            ->getJson('/api/roles/1')
            ->assertJson([
                'roles' => [2, 3, 4],
            ])
            ->assertStatus(Response::HTTP_OK);
    }

    public function testAssigningRoles()
    {
        $assigned = $this->redis()->get("game:{$this->game['id']}")['assigned_roles'];
        $this->assertEmpty($assigned);

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/roles/assign', ['gameId' => $this->game['id']])
            ->assertOk();

        $game = $this->redis()->get("game:{$this->game['id']}");

        $this->assertCount(2, $game['assigned_roles']);
        $this->assertCount(1, $game['werewolves']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->secondUser] = User::factory(2)->make();

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', ['roles' => [1, 2]])
            ->json('game');

        $this->redis()->set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);
    }
}
