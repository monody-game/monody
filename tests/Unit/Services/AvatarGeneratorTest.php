<?php

namespace Services;

use App\Models\User;
use App\Services\AvatarGenerator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarGeneratorTest extends TestCase
{
	private User $user;

	public function testGeneratingAvatar() {
		Storage::fake();
		Storage::putFileAs('avatars', base_path('tests/Helpers/avatars/1.png'), "{$this->user->id}.png");
		Storage::putFileAs('levels', base_path('tests/Helpers/levels/100.png'), "100.png");

		$image = $this->generator->generate($this->user);

		$this->assertSame('image/png', $image->mime());
		$this->assertSame(600, $image->height());
		$this->assertSame(600, $image->width());
	}

	public function testGettingAvatarOverlay() {
		$same = $this->generator->getOverlay(10);
		$below = $this->generator->getOverlay(52);
		$this->assertSame(10, $same);
		$this->assertSame(50, $below);
	}

    protected function setUp(): void
    {
		parent::setUp();

        $this->user = User::factory()->makeOne([
			'level' => 100
		]);

		$this->generator = new AvatarGenerator();
    }
}