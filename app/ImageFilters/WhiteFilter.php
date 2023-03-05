<?php

namespace App\ImageFilters;

use Intervention\Image\Facades\Image;
use Intervention\Image\Filters\FilterInterface;

class WhiteFilter implements FilterInterface
{
    const LIGHT_BG_COLOR = 'fffcf1';

    const DARK_BG_COLOR = '0f1127';

    public function __construct(
        private readonly bool $darkTheme
    ) {
    }

    public function applyFilter(\Intervention\Image\Image $image)
    {
        return Image::canvas($image->getWidth(), $image->getHeight())
            ->fill($this->darkTheme ? self::DARK_BG_COLOR : self::LIGHT_BG_COLOR)
            ->mask($image, false);
    }
}
