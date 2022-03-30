<?php

namespace App;

use App\Exceptions\FileExtensionException;
use App\Exceptions\FileLoadException;
use App\Models\User;
use const DIRECTORY_SEPARATOR;
use GdImage;

class AvatarGenerator
{
    /**
     * @var int[]
     */
    private array $overlayLevels = [
        0,
        10,
        25,
        50,
        70,
        80,
        90,
        100
    ];

    /**
     * @throws FileExtensionException
     */
    public function generate(User $user): bool
    {
        $formatted = $this->getFormattedUserAvatar($user->avatar);
        $base = $this->getImageDependingOnExtension($formatted);
        $overlayPath = $this->getOverlay();

        if (false === $overlayPath) {
            return false;
        }

        $overlay = $this->getImageDependingOnExtension($overlayPath);
        /** @var GdImage $overlay */
        $overlay = imagescale($overlay, 600, 600);
        /** @var GdImage $base */
        $base = imagescale($base, 600, 600);

        imagecopy(
            $base,
            $overlay,
            imagesx($overlay) - imagesx($base),
            imagesy($overlay) - imagesy($base),
            0,
            0,
            imagesx($overlay),
            imagesy($overlay)
        );

        imagejpeg($base, $this->getGeneratedPath() . DIRECTORY_SEPARATOR . $user->id . '.jpg');

        imagedestroy($base);

        return true;
    }

    private function getFormattedUserAvatar(string $baseAvatar): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . $baseAvatar;
    }

    /**
     * @throws FileExtensionException
     * @throws FileLoadException
     */
    private function getImageDependingOnExtension(string $filename): GdImage
    {
        $image = match (mime_content_type($filename)) {
            'image/jpeg' => imagecreatefromjpeg($filename),
            'image/png' => imagecreatefrompng($filename),
            default => throw new FileExtensionException(explode('.', $filename)[1]),
        };

        if ($image) {
            return $image;
        }

        throw new FileLoadException($filename);
    }

    /**
     * @return string|false
     */
    private function getOverlay(int $level = 100)
    {
        if (!\in_array($level, $this->overlayLevels, true)) {
            return false;
        }

        return \dirname(__DIR__) .
            DIRECTORY_SEPARATOR . 'public' .
            DIRECTORY_SEPARATOR . 'images' .
            DIRECTORY_SEPARATOR . 'avatars' .
            DIRECTORY_SEPARATOR . 'levels' .
            DIRECTORY_SEPARATOR . $level . '.png';
    }

    private function getGeneratedPath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'avatars';
    }
}
