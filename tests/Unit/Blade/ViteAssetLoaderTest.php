<?php

namespace Tests\Unit\Blade;

use App\Blade\ViteAssetLoader;
use Tests\TestCase;

class ViteAssetLoaderTest extends TestCase
{
	public function testGettingAnAsset() {
		$devLoader = new ViteAssetLoader(true);
		$prodLoader = new ViteAssetLoader(false);

		$devTag = <<<HTML
	<script src="https://localhost:3000/test" type="module" defer></script>
HTML;
		$prodTag = <<<HTML
	<script src="/assets/test" type="module" defer></script>
HTML;

		$this->assertSame(
			$devTag,
			$devLoader->asset('test')
		);
		$this->assertSame(
			$prodTag,
			$prodLoader->asset('test')
		);
	}
}
