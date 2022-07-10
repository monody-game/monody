<?php

namespace App\Services;

use App\Events\GameKill;
use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Traits\MemberHelperTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class VoteService
{
    use MemberHelperTrait;

    /**
     * @return array<int, array<int>>
     */
    public function vote(string $userId, string $gameId, ?string $votingUser = null): array
    {
        if ($this->isDead($userId, $gameId)) {
            return [];
        }

        $votes = $this->getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();

        if (\array_key_exists($userId, $votes) && \in_array($authUserId, $votes[$userId], true)) {
            return $this->unvote($userId, $gameId);
        }

        GameVote::dispatch([
            'votedUser' => $userId,
            'gameId' => $gameId,
            'votedBy' => $authUserId
        ]);

        $votes[$userId][] = $authUserId;

        Redis::set("game:$gameId:votes", json_encode($votes));

        return $votes;
    }

    /**
     * @return array<int, array<int>>
     */
    public function unvote(string $userId, string $gameId, ?string $votingUser = null): array
    {
        $votes = $this->getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();

        GameUnvote::dispatch([
            'votedUser' => $userId,
            'gameId' => $gameId,
            'votedBy' => $authUserId
        ]);

        /** @var int $userIndex */
        $userIndex = array_search($authUserId, $votes[$userId], true);

        array_splice($votes[$userId], $userIndex, 1);

        if ([] === $votes[$userId]) {
            unset($votes[$userId]);
        }

        Redis::set("game:$gameId:votes", json_encode($votes));

        return $votes;
    }

    /**
     * @return string|false vote cancelled or not any player to vote
     */
    public function afterVote(string $gameId): string|false
    {
        $votes = $this->getVotes($gameId);

        if ([] === $votes) {
            GameKill::dispatch([
                'killedUser' => null,
                'gameId' => $gameId,
            ]);

            return false;
        }

        $majority = array_key_first($votes);

        foreach ($votes as $voted => $by) {
            if (\count($by) > \count($votes[$majority])) {
                $majority = $voted;
                continue;
            }

            if (\count($by) === \count($votes[$majority])) {
                $toRandomPick = [
                    $majority,
                    $voted
                ];

                $majority = $toRandomPick[random_int(0, 1)];
            }
        }

        if ($this->isDead($majority, $gameId)) {
            GameKill::dispatch([
                'killedUser' => null,
                'gameId' => $gameId,
            ]);

            return false;
        }

        $this->kill($majority, $gameId);

        GameKill::dispatch([
            'killedUser' => $majority,
            'gameId' => $gameId,
        ]);

        $this->clearVotes($gameId);

        return $majority;
    }

    private function clearVotes(string $gameId): void
    {
        Redis::set("game:$gameId:votes", json_encode([]));
    }

    private function getVotes(string $gameId): array
    {
        return json_decode(Redis::get("game:$gameId:votes"), true) ?? [];
    }

    public function kill(string $userId, string $gameId): bool
    {
        $member = $this->getMember($userId, $gameId);
        $members = $this->getMembers($gameId);
        $index = array_search($member, $members, true);

        if (!$member || false === $index) {
            return false;
        }

        $member = array_splice($members, (int) $index, 1)[0];

        $member['user_info']['is_dead'] = true;
        $members = [...$members, $member];

        Redis::set("game:$gameId:members", json_encode($members));

        return true;
    }

    public function isDead(string $userId, string $gameId): bool
    {
        $member = $this->getMember($userId, $gameId);

        if (!$member) {
            return true;
        }

        if (
            \array_key_exists('is_dead', $member['user_info']) &&
            true === $member['user_info']['is_dead']
        ) {
            return true;
        }

        return false;
    }
}
