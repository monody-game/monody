<?php

namespace App\Providers;

use App\Blade\ViteAssetLoader;
use App\Services\RedisService;
use Illuminate\Support\Facades\Vite;
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
		Vite::useScriptTagAttributes([
			'defer' => true
		]);

        $this->app->singleton(RedisService::class, function () {
            return new RedisService();
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
