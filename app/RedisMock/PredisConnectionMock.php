<?php

namespace App\RedisMock;

use Illuminate\Redis\Connections\PredisConnection;
use Redis;
use RedisException;

class PredisConnectionMock extends PredisConnection
{
    /**
     * Execute commands in a pipeline.
     *
     *
     * @throws RedisException
     */
    public function pipeline(?callable $callback = null): array|Redis
    {
        $pipeline = $this->client()->pipeline();

        return is_null($callback)
            ? $pipeline
            : tap($pipeline, $callback)->exec();
    }
}
