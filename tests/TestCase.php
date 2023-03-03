<?php

namespace Tests;

use App\Contracts\RedisInterface;
use App\Contracts\RedisMock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->singleton(RedisInterface::class, RedisMock::class);

        Artisan::call('migrate', ['-vvv' => true]);
        Artisan::call('db:seed', ['-vvv' => true]);
    }
}
