<?php

namespace Tests\Unit\Facades;

use App\Facades\ViteFacade;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ViteFacadeTest extends TestCase
{
	public function testCallingFacadeInView() {
		$tag = <<<HTML
	<script src="https://localhost:3000/test" type="module" defer></script>
HTML;
		ViteFacade::shouldReceive('asset')
			->once()
			->with('testFile')
			->andReturn($tag);

		Blade::render("{!! Vite::asset('testFile') !!}", deleteCachedView: true);
	}
}
