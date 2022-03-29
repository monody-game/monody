<?php

namespace App;

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

    public function generate(User $user): bool
    {
        $formatted = $this->getFormattedUserAvatar($user->avatar);

        if ('image/jpeg' === mime_content_type($formatted)) {
            /** @var GdImage $base */
            $base = imagecreatefromjpeg($formatted);
        } elseif ('image/png' === mime_content_type($formatted)) {
            /** @var GdImage $base */
            $base = imagecreatefrompng($formatted);
        } else {
            /** @var GdImage $base */
            $base = imagecreate(600, 600);
        }

        $overlayPath = $this->getOverlay();

        if (false === $overlayPath) {
            return false;
        }

        /** @var GdImage $overlay */
        $overlay = imagecreatefrompng($overlayPath);
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

    private function getGeneratedPath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'avatars';
    }

    private function getFormattedUserAvatar(string $baseAvatar): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . $baseAvatar;
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
}
