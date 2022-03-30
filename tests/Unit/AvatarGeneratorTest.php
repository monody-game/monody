<?php

namespace Tests\Unit;

use App\AvatarGenerator;
use App\Exceptions\FileExtensionException;
use App\Exceptions\FileLoadException;
use App\Models\User;
use GdImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function dirname;

class AvatarGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath;
    private AvatarGenerator $avatarGenerator;

    public function testFormattedUserAvatarPath(): void
    {
        $this->assertSame(
            $this->basePath . DIRECTORY_SEPARATOR . '1.png',
            $this->avatarGenerator->getFormattedUserAvatar($this->user->avatar)
        );
    }

    public function testSupportedExtensionReturnsGDImage(): void
    {
        $path1 = $this->avatarGenerator->getFormattedUserAvatar($this->user->avatar);
        $path2 = $this->avatarGenerator->getFormattedUserAvatar('/images/avatars/2.jpg');
        $this->assertInstanceOf(GdImage::class, $this->avatarGenerator->getImageDependingOnExtension($path1));
        $this->assertInstanceOf(GdImage::class, $this->avatarGenerator->getImageDependingOnExtension($path2));
    }

    public function testUnsupportedExtensionThrowsException(): void
    {
        $this->expectException(FileExtensionException::class);
        $path = $this->avatarGenerator->getFormattedUserAvatar('/images/avatars/1.gif');
        $this->avatarGenerator->getImageDependingOnExtension($path);
    }

    public function testUnExistantFileThrowsException(): void
    {
        $this->expectException(FileLoadException::class);
        $path = $this->avatarGenerator->getFormattedUserAvatar('/images/avatars/10.jpg');
        $this->avatarGenerator->getImageDependingOnExtension($path);
    }

    public function testCorrectOverlayLoading(): void
    {
        $this->assertSame($this->avatarGenerator->getOverlay(), $this->basePath .
            DIRECTORY_SEPARATOR . 'levels' .
            DIRECTORY_SEPARATOR . '100.png');
    }

    public function testNotInRangeLevelOverlay(): void
    {
        $this->assertFalse($this->avatarGenerator->getOverlay(23));
    }

    public function testGeneratingAvatarWithWrongLevelOverlay(): void
    {
        $this->assertFalse($this->avatarGenerator->generate($this->user));
    }

    public function testGeneratingAvatar(): void
    {
        $user = User::factory()->make([
            'level' => 100
        ]);
        $this->assertTrue($this->avatarGenerator->generate($user));
        $this->assertFileExists($this->basePath . DIRECTORY_SEPARATOR . $user->id . '.jpg');
        unlink($this->basePath . DIRECTORY_SEPARATOR . $user->id . '.jpg');
    }

    public function testGeneratedAvatarIsCorrect(): void
    {
        $user = User::factory()->make([
            'id' => 4,
            'level' => 100
        ]);
        $this->avatarGenerator->generate($user);
        $imageInfos = getimagesize($this->basePath . DIRECTORY_SEPARATOR . $user->id . '.jpg');
        $this->assertSame('600x600', "$imageInfos[0]x$imageInfos[1]");
        $this->assertSame('image/jpeg', $imageInfos['mime']);
        unlink($this->basePath . DIRECTORY_SEPARATOR . $user->id . '.jpg');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->basePath = dirname(__DIR__) .
            DIRECTORY_SEPARATOR . 'Helpers' .
            DIRECTORY_SEPARATOR . 'avatars';
        $this->user = User::factory()->make();
        $this->avatarGenerator = new AvatarGenerator($this->basePath);
    }
}
