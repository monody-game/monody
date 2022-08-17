<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Exp;
use App\Models\User;
use Tests\TestCase;

class ExpControllerTest extends TestCase
{
    public function testGetExp(): void
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/exp/get');
        $response
            ->assertJsonPath('experience.user_id', $this->user->id)
            ->assertJsonPath('experience.exp', 15);
    }

    public function testGettingExpWithoutHavingSome()
    {
        $this->assertNull(Exp::select('*')->where('user_id', $this->secondUser->id)->get()->first());

        $response = $this->actingAs($this->secondUser, 'api')->getJson('/api/exp/get');
        $response
            ->assertJsonPath('experience.user_id', $this->secondUser->id)
            ->assertJsonPath('experience.exp', 0);

        $created = Exp::select('*')->where('user_id', $this->secondUser->id)->get()->first();
        $this->assertSame(0, $created->exp);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();
        Exp::factory()->create([
            'user_id' => $this->user->id,
            'exp' => 15,
        ]);
    }
}
