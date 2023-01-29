<?php

namespace App\Providers;

use App\Services\RedisService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Vite::useScriptTagAttributes([
            'defer' => true,
        ]);

        $this->app->singleton(RedisService::class, function () {
            return new RedisService();
        });
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
    }
}
