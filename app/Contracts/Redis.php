<?php

namespace App\Contracts;

use Illuminate\Support\Facades\Redis as RedisFacade;
use Illuminate\Support\Str;

/**
 * {@inheritDoc}
 */
class Redis implements RedisInterface
{
    public function set(string $key, array|string|int $value): void
    {
        /** @var string $value */
        $value = json_encode($value);
        RedisFacade::set($key, $value);
    }

    public function get(string $key): mixed
    {
        /** @var string $content */
        $content = RedisFacade::get($key);

        if (Str::isJson($content)) {
            $content = json_decode($content, true);

            if (Str::isJson($content)) {
                return json_decode($content, true);
            }
        }

        return $content;
    }

    public function exists(string $key): bool
    {
        return (bool) RedisFacade::exists($key);
    }

    public function __call(string $method, array $parameters): mixed
    {
        return RedisFacade::__call($method, $parameters);
    }
}
