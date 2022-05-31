<?php

namespace Http\Controllers\Api;

use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
	public function testGettingCurrentUser() {
		$this
			->actingAs($this->user, 'api')
			->get('/api/user')
			->assertJson($this->user->toArray());
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::find(1);
	}
}
