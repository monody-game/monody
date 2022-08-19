<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllRoles(): void
    {
        $response = $this->getJson('/api/roles');
        $parsed = json_decode($response->getContent(), true)['roles'];

        $this->assertTrue(count($parsed) > 1);
    }

    public function testGetOneRole(): void
    {
        $response = $this->getJson('/api/roles/get/1');
        $response->assertJsonPath('role.id', 1);
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
    }
}
