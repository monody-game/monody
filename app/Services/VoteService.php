<?php

namespace App\Services;

use App\Events\GameKill;
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
        if (!$this->alive($userId, $gameId)) {
            return [];
        }

        $votes = self::getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();
        $isVoting = self::isVoting($userId, $votes);

        if ($this->isVotingUser($userId, $votes, $authUserId)) {
            return $this->unvote($userId, $gameId);
        } elseif ($isVoting && $isVoting !== $userId) {
            $votes = $this->unvote($isVoting, $gameId);
        }

        $votes[$userId][] = $authUserId;

        Redis::set("game:$gameId:votes", $votes);

        return $votes;
    }

    /**
     * @return array<string, array<string>>
     */
    public function unvote(string $userId, string $gameId, ?string $votingUser = null): array
    {
        $votes = self::getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();

        /** @var int $userIndex */
        $userIndex = array_search($authUserId, $votes[$userId], true);

        array_splice($votes[$userId], $userIndex, 1);

        if ([] === $votes[$userId]) {
            unset($votes[$userId]);
        }

        Redis::set("game:$gameId:votes", $votes);

        return $votes;
    }

    /**
     * @return string|false vote cancelled or not any player to vote
     */
    public function afterVote(string $gameId, string $context = 'vote'): string|false
    {
        $votes = self::getVotes($gameId);

        if ([] === $votes) {
            if ($context === 'vote') {
                GameKill::dispatch([
                    'killedUser' => null,
                    'gameId' => $gameId,
                    'context' => $context,
                ]);
            }

            return false;
        }

        $majority = self::getMajority($votes);

        if (!$this->alive($majority, $gameId)) {
            GameKill::dispatch([
                'killedUser' => null,
                'gameId' => $gameId,
                'context' => $context,
            ]);

            return false;
        }

        $this->kill($majority, $gameId, $context);
        $this->clearVotes($gameId);

        return $majority;
    }

    private function clearVotes(string $gameId): void
    {
        Redis::set("game:$gameId:votes", []);
    }

    public static function getVotes(string $gameId): array
    {
        /** @var array|null $votes */
        $votes = Redis::get("game:$gameId:votes");

        /** @var array|null $votes */
        return $votes ?? [];
    }

    public static function hasMajorityVoted(array $game): bool
    {
        $votes = self::getVotes($game['id']);
        $majority = $votes[self::getMajority($votes)];
        $voters = count(self::getVotingUsers($game['users'], $votes)) / 2;

        return round($voters) >= (count($game['users']) / 2) && count($majority) > $voters;
    }

    /**
     * Return the most voted user
     */
    public static function getMajority(array $votes): string
    {
        /** @var string $majority */
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

        return $majority;
    }

    private function isVotingUser(string $userId, array $votes, string $votingUser): bool
    {
        return array_key_exists($userId, $votes) && in_array($votingUser, $votes[$userId], true);
    }

    private static function getVotingUsers(array $users, array $votes): array
    {
        $voters = [];

        foreach ($users as $user) {
            if (self::isVoting($user, $votes)) {
                $voters[] = $user;
            }
        }

        return $voters;
    }

    private static function isVoting(string $userId, array $votes): string|false
    {
        foreach ($votes as $voted => $vote) {
            if (in_array($userId, $vote, true)) {
                return $voted;
            }
        }

        return false;
    }
}
