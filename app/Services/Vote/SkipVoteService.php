<?php

namespace App\Services\Vote;

use App\Facades\Redis;

class SkipVoteService extends VoteService
{
    public function skip(string $gameId): bool
    {
        $game = Redis::get("game:$gameId");

        return $this->hasMajorityVoted($game, 'skip');
    }
}
