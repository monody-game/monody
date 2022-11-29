<?php

namespace App\Traits;

use App\Facades\Redis;

trait GameHelperTrait
{
    public function getGame(string $gameId): array
    {
        return Redis::get("game:{$gameId}");
    }

    public function getState(string $gameId): array
    {
        return Redis::get("game:$gameId:state");
    }

    public function clearRedisKeys(string $gameId): void
    {
        Redis::del("game:$gameId");
        Redis::del("game:$gameId:members");
        Redis::del("game:$gameId:state");
        Redis::del("game:$gameId:votes");
        Redis::del("game:$gameId:interactions");
        Redis::del("game:$gameId:deaths");
    }
}
