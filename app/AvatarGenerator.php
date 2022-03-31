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

    public function __construct(private string $basePath)
    {
    }

    /**
     * @throws FileExtensionException
     * @throws FileLoadException
     */
    public function generate(User $user): bool
    {
        $formatted = $this->getFormattedUserAvatar($user->avatar);
        $base = $this->getImageDependingOnExtension($formatted);
        $overlayPath = $this->getOverlay($user->level);

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

        imagejpeg($base, $this->basePath . DIRECTORY_SEPARATOR . $user->id . '.jpg');

        imagedestroy($base);

        return true;
    }

    public function getFormattedUserAvatar(string $baseAvatar): string
    {
        $baseAvatar = str_replace('/images/avatars', '', $baseAvatar);

        return $this->basePath . $baseAvatar;
    }

    /**
     * @throws FileExtensionException
     * @throws FileLoadException
     */
    public function getImageDependingOnExtension(string $filename): GdImage
    {
        /** @var string $filetype */
        $filetype = @mime_content_type($filename);

        if ($filetype) {
            $image = match ($filetype) {
                'image/jpeg' => imagecreatefromjpeg($filename),
                'image/png' => imagecreatefrompng($filename),
                default => throw new FileExtensionException($filetype),
            };

            if ($image) {
                return $image;
            }
        }

        throw new FileLoadException($filename);
    }

    /**
     * @return string|false
     */
    public function getOverlay(int $level = 100)
    {
        if (!\in_array($level, $this->overlayLevels, true)) {
            return false;
        }

        return $this->basePath .
            DIRECTORY_SEPARATOR . 'levels' .
            DIRECTORY_SEPARATOR . $level . '.png';
    }
}
