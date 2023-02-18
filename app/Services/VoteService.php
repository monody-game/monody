<?php

namespace App\Services;

use App\Enums\Teams;
use App\Events\GameKill;
use App\Events\MayorElected;
use App\Facades\Redis;
use App\Models\User;
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
        $isVoting = self::isVoting($authUserId, $votes);

        if ($isVoting && $isVoting === $userId) {
            return $this->unvote($userId, $gameId);
        } elseif ($isVoting) {
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

    public function elect(string $gameId): string
    {
        $game = Redis::get("game:$gameId");
        $gameUsers = $game['users'];
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

    /**
     * @return string|false vote cancelled or not any player to vote
     */
    public function afterVote(string $gameId, string $context = 'vote'): string|false
    {
        $votes = self::getVotes($gameId);
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

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

        if (!$this->alive($majority, $gameId) && array_filter($deaths, fn ($death) => $death['user'] === $majority) !== []) {
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

    public function hasMajorityVoted(array $game, string $context): bool
    {
        $votes = self::getVotes($game['id']);
        $majority = self::getMajority($votes);
        $allowedVoters = count($game['users']) - count($game['dead_users']);

        if ($context === 'werewolves') {
            $allowedVoters = count(self::getUsersByTeam(Teams::Werewolves, $game['id']));
        }

        if (!$majority) {
            return false;
        }

        $voters = count(self::getVotingUsers($game['users'], $votes));

        return $voters >= ($allowedVoters / 2);
    }

    /**
     * Return the most voted user
     */
    public static function getMajority(array $votes): string
    {
        $majority = array_key_first($votes) ?? '';

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
