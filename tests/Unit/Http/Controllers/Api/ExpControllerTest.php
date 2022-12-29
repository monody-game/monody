<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Exp;
use App\Models\User;
use Tests\TestCase;

class ExpControllerTest extends TestCase
{
    public function testGetExp(): void
    {
        $this
            ->actingAs($this->user, 'api')
            ->get('/api/exp/get')
            ->assertJson([
                'user_id' => $this->user->id,
                'exp' => 15,
            ]);
    }

    public function testGettingExpWithoutHavingSome()
    {
        $this->assertNull(Exp::select('*')->where('user_id', $this->secondUser->id)->get()->first());

        $response = $this->actingAs($this->secondUser, 'api')->getJson('/api/exp/get');
        $response
            ->assertJson([
                'user_id' => $this->secondUser->id,
                'exp' => 0,
            ]);

        $created = Exp::select('*')->where('user_id', $this->secondUser->id)->get()->first();
        $this->assertSame(0, $created->exp);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->secondUser] = User::factory(2)->create();
        Exp::factory()->create([
            'user_id' => $this->user->id,
            'exp' => 15,
        ]);
    }
}
