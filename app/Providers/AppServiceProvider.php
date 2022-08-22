<?php

namespace App\Providers;

use App\Blade\ViteAssetLoader;
use App\Services\RedisService;
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
        $this->app->singleton(ViteAssetLoader::class, function () {
            return new ViteAssetLoader(true);
        });

        $this->app->singleton(RedisService::class, function ($app) {
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
