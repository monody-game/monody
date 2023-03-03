<?php

namespace App\Providers;

use App\Contracts\Redis;
use App\Contracts\RedisInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Vite::useScriptTagAttributes([
            'defer' => true,
        ]);

        $this->app->bind(RedisInterface::class, Redis::class);

        $this->app->singleton('League\Glide\Server', function () {
            $filesystem = app('Illuminate\Contracts\Filesystem\Filesystem');

            return ServerFactory::create([
                'max_image_size' => 1000 * 1000,
                'response' => new LaravelResponseFactory(app('request')),
                'source' => $filesystem->getDriver(),
                'cache' => $filesystem->getDriver(),
                'cache_path_prefix' => '.cache',
                'base_url' => 'img',
            ]);
        });
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
    }
}
