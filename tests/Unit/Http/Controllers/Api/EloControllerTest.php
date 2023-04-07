<?php

namespace Http\Controllers\Api;

use App\Models\Elo;
use App\Models\User;
use Tests\TestCase;

class EloControllerTest extends TestCase
{
    public function testRetrievingEloFromBlankUser()
    {
        $user = User::factory()->createOne();

		$expected = new Elo();
		$expected->user_id = $user->id;
		$expected->save();
		$expected = $expected->fresh();

        $this
			->actingAs($user)
			->get('/api/elo')
			->assertJsonPath('data.elo', $expected->toArray());
    }
}
