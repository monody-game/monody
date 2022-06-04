<?php

namespace App;

use App\Events\GameUnvote;
use App\Events\GameVote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class VoteService
{
    public function vote(int $userId, string $gameId): void
    {
        $votes = $this->getVotes($gameId);
        $authUserId = Auth::user()?->getAuthIdentifier();

        if (\array_key_exists($userId, $votes) && \in_array($authUserId, $votes[$userId], true)) {
            $this->unvote($userId, $gameId);

            return;
        }

        GameVote::dispatch([
            'userId' => $userId,
            'gameId' => $gameId
        ]);

        $votes[$userId][] = $authUserId;

        Redis::set("game:$gameId:votes", json_encode($votes));
    }

    public function unvote(int $userId, string $gameId): void
    {
        $votes = $this->getVotes($gameId);
        $authUserId = Auth::user()?->getAuthIdentifier();

        if (\array_key_exists($userId, $votes) && !\in_array($authUserId, $votes[$userId], true)) {
            $this->vote($userId, $gameId);
        }

        GameUnvote::dispatch([
            'userId' => $userId,
            'gameId' => $gameId
        ]);

        /** @var int $userIndex */
        $userIndex = array_search($authUserId, $votes[$userId], true);

        array_splice($votes[$userId], $userIndex, 1);

        Redis::set("game:$gameId:votes", json_encode($votes));
    }

    private function getVotes(string $gameId): array
    {
        return json_decode(Redis::get("game:$gameId:votes"), true) ?? [];
    }
}
