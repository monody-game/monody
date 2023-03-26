<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllTeams(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/teams');
        $response->assertJsonCount(3, 'data.teams');
    }

    public function testRetrievingOneTeam(): void
    {
        $id = Team::Villagers->value;

        $this
            ->actingAs($this->user, 'api')
            ->getJson("/api/team/{$id}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'team' => [
                        'id' => $id,
                        'name' => Team::Villagers->name(),
                        'display_name' => Team::Villagers->stringify(),
                    ],
                ],
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
    }
}
