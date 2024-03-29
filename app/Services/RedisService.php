<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class RedisService extends Redis
{
    public function set(string $key, array|string|int $value): void
    {
        /** @var string $value */
        $value = json_encode($value);
        parent::set($key, $value);
    }

    public function get(string $key): mixed
    {
        /** @var string $content */
        $content = parent::get($key);

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
        return (bool) parent::exists($key);
    }

    /**
     * Update a key using the callback given
     */
    public function update(string $key, callable $callback): mixed
    {
        $value = $this->get($key) ?? []; // Allow to pass by reference
        $old = $value;

        $updated = $callback($value);

        if ($old !== $value) {
            $updated = $value;
        }

        $this->set($key, $updated);

        return $updated;
    }

    public function __call(string $method, array $parameters): mixed
    {
        return parent::__call($method, $parameters);
    }
}
