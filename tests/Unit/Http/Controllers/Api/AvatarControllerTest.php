<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AvatarControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function testUploadingAvatar(): void
    {
        $this->assertSame('/assets/avatars/default_1.png', $this->user->avatar);

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/avatars', [
                'avatar' => UploadedFile::fake()->image('avatartest.png', 400, 400),
            ])
            ->assertStatus(Response::HTTP_CREATED);

        Storage::assertExists("avatars/{$this->user->id}.png");
        Storage::assertExists('avatars/default_1.png');
        Storage::delete("avatars/{$this->user->id}.png");

        $this->assertSame("/assets/avatars/{$this->user->id}.png", $this->user->avatar);
    }

    public function testDeletingAvatar(): void
    {
        $this
            ->actingAs($this->user, 'api')
            ->post('/api/avatars', [
                'avatar' => UploadedFile::fake()->image('avatartest.png', 400, 400),
            ])
            ->assertStatus(Response::HTTP_CREATED);

        Storage::assertExists("avatars/{$this->user->id}.png");
        $this->assertSame("/assets/avatars/{$this->user->id}.png", $this->user->avatar);

        $this
            ->actingAs($this->user, 'api')
            ->delete('/api/avatars')
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Storage::assertMissing("avatars/{$this->user->id}.png");

        $this->assertMatchesRegularExpression('/^\/assets\/avatars\/default_[0-9]+\.png$/', $this->user->avatar);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->make([
            'level' => 100,
        ]);
    }
}
