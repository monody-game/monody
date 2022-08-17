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

    public function set(string $key, mixed $value): void
    {
        if (!\in_array($key, $this->keys, true)) {
            return;
        }

        $this->data[$key] = $value;
    }

    public function setKeys(array $keys): void
    {
        $this->keys = array_merge($keys, $this->keys);
    }
}
