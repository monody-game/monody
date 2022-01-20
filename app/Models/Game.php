<?php

namespace App\Models;

class Game extends Entity
{
    protected array $keys = [
        'id',
        'owner_id',
        'users',
        'roles',
        'is_started',
    ];

    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
