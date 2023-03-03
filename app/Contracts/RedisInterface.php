<?php

namespace App\Contracts;

interface RedisInterface
{
    public function get(string $key): mixed;

    public function set(string $key, array|int|string $value): void;

    public function exists(string $key): bool;
}
