<?php

namespace App\Providers;

use App\Blade\ViteAssetLoader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->singleton(ViteAssetLoader::class, function ($app) {
			return new ViteAssetLoader(true);
		});
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
