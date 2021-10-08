<?php

namespace App;

use App\Models\User;
use const DIRECTORY_SEPARATOR;

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
            /** @var resource $base */
            $base = imagecreatefromjpeg($formatted);
        } elseif ('image/png' === mime_content_type($formatted)) {
            /** @var resource $base */
            $base = imagecreatefrompng($formatted);
        }

        $overlayPath = $this->getOverlay();

        if (false === $overlayPath) {
            return false;
        }

        /** @var resource $overlay */
        /** @var string $overlayPath */
        $overlay = imagecreatefrompng($overlayPath);
        /** @var resource $overlay */
        $overlay = imagescale($overlay, 600, 600);
        /** @var resource $base */
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
     * @return bool|string
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
