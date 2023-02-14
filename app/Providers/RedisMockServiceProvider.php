<?php

namespace App\Providers;

use App\RedisMock\PredisConnectorMock;
use Illuminate\Support\ServiceProvider;

class RedisMockServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->make('redis')->extend('mock', function () {
            return new PredisConnectorMock();
        });
    }
}
