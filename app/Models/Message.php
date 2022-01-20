<?php

namespace App\Models;

class Message extends Entity
{
    protected array $keys = ['gameId', 'author', 'content'];

    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
