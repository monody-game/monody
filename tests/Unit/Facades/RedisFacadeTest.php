<?php

namespace Tests\Unit\Facades;

use App\Facades\Redis;
use Tests\TestCase;

class RedisFacadeTest extends TestCase
{
    public function testCallingRedisMethod()
    {
        Redis::set('test', [
            1, 2,
        ]);

        $this->assertSame([1, 2], Redis::get('test'));
    }
}
