<?php

namespace Tests\Unit\Http\Middleware;

use App\Models\User;
use Tests\TestCase;

class OptionalAuthenticationTest extends TestCase
{
    public function testSendingRequestWhileBeingAuthenticated()
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user, 'api')
            ->get('/api/stats')
            ->assertOk();
    }

    public function testSendingRequestWhileNotBeingAuthenticated()
    {
        $user = User::factory()->create();

        $this
            ->get("/api/stats/{$user->id}")
            ->assertOk();
    }
}
