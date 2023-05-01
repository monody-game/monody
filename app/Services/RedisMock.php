<?php

namespace App\Services;

use Illuminate\Support\Str;

class RedisMock
{
    public array $data = [];

    public function data(): array
    {
        return $this->data;
    }

    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            return [];
        }

        $content = $this->data[$key];

        if (Str::isJson($content)) {
            $content = json_decode($content, true);

            if (Str::isJson($content)) {
                return json_decode($content, true);
            }
        }

        return $content;
    }

    public function set(string $key, array|string|int $value): void
    {
        $this->data[$key] = json_encode($value);
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function del(string ...$keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->data)) {
                continue;
            }

            unset($this->data[$key]);
        }
    }

    public function scan(int &$cursor, array $options): array
    {
        $limit = 100;
        $pattern = '*';

        if (array_key_exists('COUNT', $options)) {
            $limit = $options['COUNT'];
        }

        if (array_key_exists('MATCH', $options)) {
            $pattern = $options['MATCH'];
        }

        return [
            $cursor,
            array_filter(
                array_keys(array_slice($this->data, 0, $limit)),
                fn ($key) => fnmatch($pattern, $key)
            ),
        ];
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
}
