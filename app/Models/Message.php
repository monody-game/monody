<?php

namespace App\Models;

class Message extends ObjectData
{
    protected array $keys = ['gameId', 'author', 'content'];

    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
