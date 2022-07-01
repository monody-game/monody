<?php

namespace Tests\Unit\Http\Middleware;

use App\Models\User;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
	public function testRequestingRestrictedRoute() {
		$this
			->get('/api/user')
			->assertExactJson(['message' => 'Unauthenticated.'])
			->assertUnauthorized();
	}

	public function testRequestingRestrictedRouteWhileBeingAuthenticated() {
		$user = User::factory()->create();

		$this
			->actingAs($user, 'api')
			->get('/api/user')
			->assertOk();
	}
}
