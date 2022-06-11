<?php

namespace Http\Controllers\Api;

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

    public function testGenerateCallService(): void
    {
		Storage::fake();
		Storage::putFileAs('avatars', base_path('tests/Helpers/avatars/1.png'), "{$this->user->id}.png");
		Storage::putFileAs('levels', base_path('tests/Helpers/levels/100.png'), "100.png");

        $this
			->actingAs($this->user, 'api')
			->getJson('/api/avatars/generate')
			->assertStatus(Response::HTTP_NO_CONTENT);

		Storage::assertExists("avatars/{$this->user->id}.png");
		$this->assertSame("/storage/avatars/{$this->user->id}.png", $this->user->avatar);
    }

	public function testUploadingAvatar(): void
	{
		Storage::fake();

		$this->assertSame("/storage/avatars/{$this->user->id}.png", $this->user->avatar);

		$this
			->actingAs($this->user, 'api')
			->put('/api/avatars/upload', [
				'avatar' => UploadedFile::fake()->image('avatartest.png', 400, 400)
			])
			->assertStatus(Response::HTTP_CREATED);

		Storage::assertExists("avatars/{$this->user->id}.png");

		$this->assertSame("/storage/{$this->user->id}", $this->user->avatar);
	}

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make([
            'level' => 100
        ]);
    }
}
