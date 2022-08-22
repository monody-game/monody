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
}
