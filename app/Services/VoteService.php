<?php

namespace App\Services;

use App\Events\GameKill;
use App\Events\GameUnvote;
use App\Events\GameVote;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use function array_key_exists;
use function count;
use Illuminate\Support\Facades\Auth;
use function in_array;

class VoteService
{
    use MemberHelperTrait;

    /**
     * @return array<string, array<string>>
     */
    public function vote(string $userId, string $gameId, ?string $votingUser = null): array
    {
        if ($this->isDead($userId, $gameId)) {
            return [];
        }

        $votes = $this->getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();

        if ($this->isVotingUser($userId, $votes, $authUserId)) {
            return $this->unvote($userId, $gameId);
        }

        $isVoting = $this->isVoting($userId, $votes);

        if ($isVoting) {
            $votes = $this->unvote($isVoting, $gameId);
        }

        GameVote::dispatch([
            'votedUser' => $userId,
            'gameId' => $gameId,
            'votedBy' => $authUserId,
        ]);

        $votes[$userId][] = $authUserId;

        Redis::set("game:$gameId:votes", $votes);

        return $votes;
    }

    /**
     * @return array<string, array<string>>
     */
    public function unvote(string $userId, string $gameId, ?string $votingUser = null): array
    {
        $votes = $this->getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();

        /** @var int $userIndex */
        $userIndex = array_search($authUserId, $votes[$userId], true);

        array_splice($votes[$userId], $userIndex, 1);

        if ([] === $votes[$userId]) {
            unset($votes[$userId]);
        }

        GameUnvote::dispatch([
            'votedUser' => $userId,
            'gameId' => $gameId,
            'votedBy' => $authUserId,
        ]);

        Redis::set("game:$gameId:votes", $votes);

        return $votes;
    }

    /**
     * @param  string  $context How the player was killed (vote, ...)
     * @return string|false vote cancelled or not any player to vote
     */
    public function afterVote(string $gameId, string $context): string|false
    {
        $votes = $this->getVotes($gameId);

        if ([] === $votes) {
            GameKill::dispatch([
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => $context,
            ]);

            return false;
        }

        $majority = array_key_first($votes);

        foreach ($votes as $voted => $by) {
            if (count($by) > count($votes[$majority])) {
                $majority = $voted;

                continue;
            }

            if (count($by) === count($votes[$majority])) {
                $toRandomPick = [
                    $majority,
                    $voted,
                ];

                $majority = $toRandomPick[random_int(0, 1)];
            }
        }

        if ($this->isDead($majority, $gameId)) {
            GameKill::dispatch([
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => $context,
            ]);

            return false;
        }

        $this->kill($majority, $gameId);

        GameKill::dispatch([
            'killedUser' => $majority,
            'gameId' => $gameId,
            'context' => $context,
        ]);

        $this->clearVotes($gameId);

        return $majority;
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

        Redis::set("game:$gameId:members", $members);

        return true;
    }

    public function isDead(string $userId, string $gameId): bool
    {
        $member = $this->getMember($userId, $gameId);

        if (!$member) {
            return true;
        }

        if (
            array_key_exists('is_dead', $member['user_info']) &&
            true === $member['user_info']['is_dead']
        ) {
            return true;
        }

        return false;
    }

    private function clearVotes(string $gameId): void
    {
        Redis::set("game:$gameId:votes", []);
    }

    private function getVotes(string $gameId): array
    {
        /** @var array|null|string $votes */
        $votes = Redis::get("game:$gameId:votes");

        if ($votes === '') {
            return [];
        }

        /** @var array|null $votes */
        return $votes ?? [];
    }

    /**
     * @param  array<string, array<string>>  $votes
     */
    private function isVotingUser(string $userId, array $votes, string $votingUser): bool
    {
        return array_key_exists($userId, $votes) && in_array($votingUser, $votes[$userId], true);
    }

    private function isVoting(string $userId, array $votes): string|false
    {
        foreach ($votes as $voted => $vote) {
            if (in_array($userId, $vote, true)) {
                return $voted;
            }
        }

        return false;
    }
}
