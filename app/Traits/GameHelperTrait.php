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
        Redis::del(
            "game:$gameId",
            "game:$gameId:members",
            "game:$gameId:state",
            "game:$gameId:votes",
            "game:$gameId:interactions",
            "game:$gameId:interactions:usedActions",
            "game:$gameId:deaths",
            "game:$gameId:discord",
        );
    }
}
