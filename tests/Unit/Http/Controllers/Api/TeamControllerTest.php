<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllTeams(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/teams');
        $response->assertJsonCount(3, 'teams');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
    }
}
