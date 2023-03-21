<?php

namespace App\Facades;

use App\Services\RedisService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void set(string $key, array|int|string $value)
 * @method static mixed get(string $key)
 * @method static void del(string $key)
 * @method static bool exists(string $key)
 * @method static array scan(int &$cursor, array $params)
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
