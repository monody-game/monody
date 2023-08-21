<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Exp;
use App\Models\User;
use Tests\TestCase;

class ExpControllerTest extends TestCase
{
    public function testRetrievingUserExp(): void
    {
        $user = User::factory([
            'level' => 2,
        ])->create();

        Exp::factory()->create([
            'user_id' => $user->id,
            'exp' => 15,
        ]);

        $this
            ->actingAs($user, 'api')
            ->get('/api/exp')
            ->assertJsonPath('data.exp', [
                'user_id' => $user->id,
                'exp' => 15,
                'next_level' => 32,
            ]);
    }

    public function testGettingExpWithoutHavingSome()
    {
        $user = User::factory()->create();

        $this->assertNull(Exp::select('*')->where('user_id', $user->id)->get()->first());

        $response = $this->actingAs($user, 'api')->getJson('/api/exp');
        $response
            ->assertJsonPath('data.exp', [
                'user_id' => $user->id,
                'exp' => 0,
                'next_level' => 10,
            ]);

        $created = Exp::select('*')->where('user_id', $user->id)->get()->first();
        $this->assertSame(0, $created->exp);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
