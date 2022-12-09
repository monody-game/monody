<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Teams;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllTeams(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/teams');
        $response->assertJsonCount(2, 'teams');
    }

    public function testRetrievingOneTeam(): void
    {
        $id = Teams::Villagers->value;

        $this
            ->actingAs($this->user, 'api')
            ->getJson("/api/team/{$id}")
            ->assertOk()
            ->assertExactJson([
                'team' => [
                    'id' => $id,
                    'name' => Teams::Villagers->name(),
                    'display_name' => Teams::Villagers->stringify(),
                ],
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
    }
}
