<?php

namespace App\Facades;

use App\Services\RedisService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\RedisService
 * @see \Illuminate\Redis\RedisManager
 */
class Redis extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RedisService::class;
    }
}
