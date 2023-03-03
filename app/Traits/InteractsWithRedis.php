<?php

namespace App\Traits;

use App\Contracts\RedisInterface;

trait InteractsWithRedis
{
    public function redis(): RedisInterface
    {
        return app()->make(RedisInterface::class);
    }
}
