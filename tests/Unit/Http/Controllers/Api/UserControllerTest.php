<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testGettingCurrentUser()
    {
        $this
            ->actingAs($this->user, 'api')
            ->get('/api/user')
            ->assertJson($this->user->toArray());
    }

    public function testUpdatingUser()
    {
        $user = User::factory()->makeOne([
            'username' => 'John',
            'email' => 'testemail@test.com',
        ]);

        $this
            ->actingAs($user, 'api')
            ->patch('/api/user', [
                'email' => 'emailtest@test.com',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $user->id,
                'username' => 'John',
                'email' => 'emailtest@test.com',
            ]);

        $user = $user->refresh();

        $this->assertSame('John', $user->username);
        $this->assertSame('emailtest@test.com', $user->email);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
}
