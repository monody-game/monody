<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Teams;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $secondUser;

    private array $game;

    public function testGetAllRoles(): void
    {
        $response = $this->getJson('/api/roles');
        $parsed = json_decode($response->getContent(), true)['roles'];

        $this->assertTrue(count($parsed) > 1);
    }

    public function testGetOneRole(): void
    {
        $response = $this->getJson('/api/roles/get/1');
        $response->assertExactJson([
            'role' => [
                'id' => 1,
                'name' => 'werewolf',
                'display_name' => 'Loup-garou',
                'image' => '/images/roles/werewolf.png',
                'limit' => null,
                'weight' => 2,
                'team_id' => Teams::Werewolves->value,
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
        $assigned = Redis::get("game:{$this->game['id']}")['assigned_roles'];
        $this->assertEmpty($assigned);

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/roles/assign', ['gameId' => $this->game['id']])
            ->assertOk();

        $game = Redis::get("game:{$this->game['id']}");

        $this->assertCount(2, $game['assigned_roles']);
        $this->assertCount(1, $game['werewolves']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->secondUser] = User::factory(2)->make();

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->post('/api/game/new', ['roles' => [1, 2]])
            ->json('game');

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);
    }
}
