<?php

namespace App\Models;

class Entity
{
    protected array $keys = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function all(): array
    {
        return $this->data;
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        if (!\in_array($key, $this->keys, true)) {
            return;
        }

        $this->data[$key] = $value;
    }

    public function get(string $key): ?string
    {
        if (!\in_array($key, $this->keys, true)) {
            return null;
        }

        return $this->data[$key] ?? null;
    }
}
