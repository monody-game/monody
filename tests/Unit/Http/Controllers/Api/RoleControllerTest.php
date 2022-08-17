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
        $response = $this->actingAs($this->user, 'api')->getJson('/api/roles');
        $parsed = json_decode($response->getContent(), true)['roles'];

        $this->assertTrue(count($parsed) > 1);
    }

    public function testGetOneRole(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/roles/get/1');
        $response->assertJsonPath('role.id', 1);
    }

    public function testGetUnexistentRole(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/roles/get/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
    }
}
