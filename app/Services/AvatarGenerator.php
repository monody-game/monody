<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Image;

class AvatarGenerator
{
    /**
     * @var int[]
     */
    private array $overlayLevels = [
        10,
        25,
        50,
        75,
        100
    ];

    /**
     * Generate brand-new user's avatar, with the overlay corresponding to his level.
     */
    public function generate(User $user): Image
    {
        $overlayLevel = Storage::get('levels/' . $this->getOverlay($user->level) . '.png');
        $storageAvatar = Storage::get($this->toStoragePath($user->avatar));

        $avatar = ImageFacade::make($storageAvatar)->resize(600, 600);
        $overlay = ImageFacade::make($overlayLevel)->resize(600, 600);

        $avatar->insert($overlay);
        $avatar->encode('png');

        return $avatar;
    }

    /**
     * Return the closest level overlay.
     */
    public function getOverlay(int $level): int
    {
        if (\in_array($level, $this->overlayLevels, true)) {
            return $level;
        }

        foreach ($this->overlayLevels as $key => $overlayLevel) {
            if ($level > $overlayLevel) {
                continue;
            }

            if (
                $level < $overlayLevel &&
                \array_key_exists($key - 1, $this->overlayLevels) &&
                $this->overlayLevels[$key - 1] > $level === false
            ) {
                return $this->overlayLevels[$key - 1];
            }
        }

        return 0;
    }

    public function toStoragePath(string $path): string
    {
        return str_replace('/storage/', '', $path);
    }
}
