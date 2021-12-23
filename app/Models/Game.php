<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

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
        Log::info(json_encode($this->all()));
    }
}
