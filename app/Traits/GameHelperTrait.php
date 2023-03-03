<?php

namespace App\Traits;

trait GameHelperTrait
{
    use InteractsWithRedis;

    public function getGame(string $gameId): array
    {
        return $this->redis()->get("game:{$gameId}");
    }

    public function getState(string $gameId): array
    {
        return $this->redis()->get("game:$gameId:state");
    }

    public function clearRedisKeys(string $gameId): void
    {
        $this->redis()->del("game:$gameId");
        $this->redis()->del("game:$gameId:members");
        $this->redis()->del("game:$gameId:state");
        $this->redis()->del("game:$gameId:votes");
        $this->redis()->del("game:$gameId:interactions");
        $this->redis()->del("game:$gameId:interactions:usedActions");
        $this->redis()->del("game:$gameId:deaths");
    }
}
