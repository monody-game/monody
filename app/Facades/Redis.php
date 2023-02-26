<?php

namespace App\Facades;

use App\Services\RedisService;
use Illuminate\Support\Facades\Facade;

/**
 * @method void set(string $key, array|int|string $value)
 * @method mixed get(string $key)
 * @method bool exists(string $key)
 *
 * @see RedisService
 * @see \Illuminate\Redis\RedisManager
 */
class Redis extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RedisService::class;
    }
}
