<?php

namespace App\Models;

class Game extends ObjectData
{
    protected array $keys = [
        'id',
        'owner',
        'users',
        'roles',
        'is_started',
    ];

    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
