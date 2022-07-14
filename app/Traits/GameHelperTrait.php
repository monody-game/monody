<?php

namespace App\Traits;

use Illuminate\Support\Facades\Redis;

trait GameHelperTrait
{
    public function getGame(string $gameId): array
    {
        $game = Redis::get("game:{$gameId}");

        return json_decode($game, true);
    }

    public function getState(string $gameId): array
    {
        $game = Redis::get("game:$gameId:state");

        return json_decode($game, true);
    }
}
