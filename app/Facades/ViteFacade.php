<?php

namespace App\Facades;

use App\Blade\ViteAssetLoader;
use Illuminate\Support\Facades\Facade;

class ViteFacade extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return ViteAssetLoader::class;
	}
}
