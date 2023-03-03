<?php

namespace App\Contracts;

use Illuminate\Redis\RedisManager;

/**
 * @method array scan(int &$cursor, array $params)
 * @method void del(string $key)
 *
 * @see RedisManager
 */
interface RedisInterface
{
    public function get(string $key): mixed;

    public function set(string $key, array|int|string $value): void;

    public function exists(string $key): bool;
}
