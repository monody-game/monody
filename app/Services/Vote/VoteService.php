<?php

namespace App\Services\Vote;

use App\Enums\Team;
use App\Events\GameKill;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;
use Illuminate\Support\Facades\Auth;

use function array_key_exists;
use function count;
use function in_array;

class VoteService
{
    use MemberHelperTrait;

    /**
     * @return array<string, array<string>>
     */
    public function vote(string $userId, string $gameId, string $votingUser = null): array
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
    public function unvote(string $userId, string $gameId, string $votingUser = null): array
    {
        $votes = self::getVotes($gameId);
        $authUserId = $votingUser ?? Auth::user()?->getAuthIdentifier();

        /** @var int $userIndex */
        $userIndex = array_search($authUserId, $votes[$userId], true);

        array_splice($votes[$userId], $userIndex, 1);

        if ($votes[$userId] === []) {
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
        $game = Redis::get("game:$gameId");
        $deaths = Redis::get("game:$gameId:deaths") ?? [];
        $mayor = '';

        if (array_key_exists('mayor', $game)) {
            $mayor = $game['mayor'];
        }

        if ($votes === []) {
            if ($context === 'vote') {
                GameKill::dispatch([
                    'killedUser' => null,
                    'gameId' => $gameId,
                    'context' => $context,
                ]);
            }

            return false;
        }

        $majority = self::getMajority($votes, $mayor);

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

    public function clearVotes(string $gameId): void
    {
        Redis::set("game:$gameId:votes", []);
    }

    /**
     * @return array Votes [ "voted user" => [ ..."voted by" ] ]
     */
    public static function getVotes(string $gameId): array
    {
        return Redis::get("game:$gameId:votes") ?? [];
    }

    public function hasMajorityVoted(array $game, string $context): bool
    {
        $votes = self::getVotes($game['id']);
        $majority = self::getMajority($votes);
        $allowedVoters = count($game['users']) - count($game['dead_users']);

        if ($context === 'werewolves') {
            $allowedVoters = count(self::getUsersByTeam(Team::Werewolves, $game['id']));
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
    public static function getMajority(array $votes, string $mayor = ''): string
    {
        $majority = array_key_first($votes) ?? '';

        foreach ($votes as $voted => $by) {
            $votersCount = count($by);

            if ($mayor !== '' && in_array($mayor, $by, true)) {
                $votersCount += 1;
            }

            if ($votersCount > count($votes[$majority])) {
                $majority = $voted;

                continue;
            }

            if ($votersCount === count($votes[$majority])) {
                $toRandomPick = [
                    $majority,
                    $voted,
                ];

                $majority = $toRandomPick[random_int(0, 1)];
            }
        }

        return $majority;
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
