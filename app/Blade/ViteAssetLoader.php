<?php

namespace App\Blade;

class ViteAssetLoader
{
    public function __construct(
        private readonly bool $isDev
    ) {
    }

    public function asset(string $url): string
    {
        if ($this->isDev) {
            return <<<HTML
	<script src="https://localhost:3000/{$url}" type="module" defer></script>
HTML;
        }

        return <<<HTML
	<script src="/assets/{$url}" type="module" defer></script>
HTML;
    }
}
