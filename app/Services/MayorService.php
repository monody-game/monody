<?php

namespace App\Services;

use App\Events\MayorElected;
use App\Facades\Redis;

class MayorService extends VoteService
{
    public function elect(string $gameId): string
    {
        $game = Redis::get("game:$gameId");
        $gameUsers = array_diff($game['users'], array_keys($game['dead_users']));
        $votes = self::getVotes($gameId);

        if ($votes === []) {
            $mayor = $gameUsers[random_int(0, count($gameUsers))];
        } else {
            $mayor = self::getMajority($votes);
        }

        $game['mayor'] = $mayor;

        Redis::set("game:$gameId", $game);

        $this->clearVotes($gameId);

        broadcast(new MayorElected([
            'gameId' => $gameId,
            'mayor' => $mayor,
        ]));

        return $mayor;
    }
}
