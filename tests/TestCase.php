<?php

namespace Tests;

use App\Facades\Redis;
use App\Services\RedisMock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'fr');

        app()->singleton(RedisMock::class, function () {
            return new RedisMock();
        });

        Redis::swap(app()->make(RedisMock::class));

        Artisan::call('migrate', ['-vvv' => true]);
        Artisan::call('db:seed', ['-vvv' => true]);
    }
}
