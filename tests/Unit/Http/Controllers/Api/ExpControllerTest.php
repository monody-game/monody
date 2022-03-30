<?php

namespace Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetExp(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/exp/get');
        $response
            ->assertJsonPath('experience.user_id', $this->user->id)
            ->assertJsonPath('experience.exp', 15);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);
    }
}
