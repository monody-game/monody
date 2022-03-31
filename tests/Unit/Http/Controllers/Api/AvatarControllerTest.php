<?php

namespace Http\Controllers\Api;

use App\Models\User;
use Tests\TestCase;

class AvatarControllerTest extends TestCase
{
    public function testGenerateCallService(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/avatars/generate');
        $response->assertStatus(200);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make([
            'level' => 100
        ]);
    }
}
