<?php

namespace App\Services;

use App\Enums\Badge;
use App\Enums\Role;
use App\Enums\Team;
use App\Events\Bot\ClearGameInvitations;
use App\Events\GameEnd;
use App\Events\GameLoose;
use App\Events\GameWin;
use App\Facades\Redis;
use App\Models\GameOutcome;
use App\Models\Statistic;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Foundation\Testing\Concerns\InteractsWithRedis;

class EndGameService
{
    use MemberHelperTrait, GameHelperTrait, InteractsWithRedis;

    public function __construct(
        private readonly ExpService $expService,
        private readonly BadgeService $badgeService
    ) {
    }

    /**
     * @param  string[]|null  $winners
     */
    public function end(string $gameId, array $winners = null): void
    {
        $winningTeam = $this->getWinningTeam($gameId);
        $winners = $winners ?? $this->getWinningUsers($gameId, $winningTeam);
        $loosers = $this->getLoosingUsers($gameId, $winners);
        $payload = [
            'gameId' => $gameId,
        ];

        broadcast(new GameEnd(array_merge($payload, [
            'winners' => $this->getFormattedWinners($winners, $gameId),
            'winningTeam' => $winningTeam,
        ])));
        broadcast(new GameWin($payload, true, $winners));
        broadcast(new GameLoose($payload, true, $loosers));

        $state = Redis::get("game:$gameId:state");

        $game = Redis::update("game:$gameId", fn (array &$game) => $game['ended'] = true);

        $outcome = new GameOutcome();
        $outcome->owner_id = $game['owner']['id'];
        $outcome->winning_role = $this->getRoleByUserId($winners[0], $gameId);
        $outcome->round = $state['round'];
        $outcome->assigned_roles = $game['roles'];
        $outcome->game_users = $game['users'];
        $outcome->winning_users = $winners;
        $outcome->save();

        foreach ([...$winners, ...$loosers] as $userId) {
            $win = in_array($userId, $winners, true);
            $stat = Statistic::firstOrCreate(['user_id' => $userId]);

            $pivotAttributes = [
                'role' => $this->getRoleByUserId($userId, $gameId),
                'win' => $win,
            ];

            if (array_key_exists($userId, $game['dead_users'])) {
                $pivotAttributes = array_merge($pivotAttributes, [
                    'death_round' => $game['dead_users'][$userId]['round'],
                    'death_context' => $game['dead_users'][$userId]['context'],
                ]);
            }

            $outcome->users()->attach(
                $userId,
                $pivotAttributes
            );

            /** @var User $user user is in game so it must be found */
            $user = User::where('id', $userId)->first();

            if ($win) {
                if (BadgeService::canAccess($user, Badge::Wins)) {
                    $this->badgeService->add($user, Badge::Wins);
                }

                $this->expService->add(50, $user);
                $stat->win_streak++;

                if ($stat->win_streak > $stat->longest_streak) {
                    $stat->longest_streak = $stat->win_streak;
                }
            } else {
                if (BadgeService::canAccess($user, Badge::Losses)) {
                    $this->badgeService->add($user, Badge::Losses);
                }

                $this->expService->add(20, $user);
                $stat->win_streak = 0;
            }

            $stat->save();

            if ($userId === $game['owner']['id']) {
                $this->expService->add(20, $user->refresh());
            }
        }

        broadcast(new ClearGameInvitations);
    }

    private function getWinningTeam(string $gameId): Team|string
    {
        $game = Redis::get("game:$gameId");
        $state = Redis::get("game:$gameId:state");
        $werewolves = array_diff($this->getUsersByTeam(Team::Werewolves, $gameId), array_keys($game['dead_users']));
        $aliveUsers = array_diff($game['users'], array_keys($game['dead_users']));
        $couple = array_key_exists('couple', $game) ? $game['couple'] : [];

        sort($aliveUsers);
        sort($couple);

        if (
            array_key_exists('couple', $game) &&
            $aliveUsers === $couple
        ) {
            return 'couple';
        }

        if (
            (
                in_array(Role::WhiteWerewolf->value, array_keys($game['roles']), true) &&
                $werewolves === $this->getUserIdByRole(Role::WhiteWerewolf, $gameId)
            ) ||
            (
                in_array(Role::Parasite->value, array_keys($game['roles']), true) &&
                $this->alive($this->getUserIdByRole(Role::Parasite, $gameId)[0], $gameId)
            )
        ) {
            return Team::Loners;
        }

        if (
            $state['round'] <= 1 &&
            array_key_exists('angel_target', $game) &&
            in_array($game['angel_target'], array_keys($game['dead_users']), true) &&
            !in_array($this->getUserIdByRole(Role::Angel, $gameId)[0], array_keys($game['dead_users']), true)
        ) {
            return Team::Loners;
        }

        if ($werewolves === []) {
            return Team::Villagers;
        }

        return Team::Werewolves;
    }

    /**
     * Must return false if the game needs to continue.
     */
    public function enoughTeamPlayersToContinue(string $gameId): bool
    {
        $game = $this->getGame($gameId);
        $villagers = $this->getUsersByTeam(Team::Villagers, $gameId);
        $werewolves = array_filter($game['werewolves'], fn ($werewolf) => $this->alive($werewolf, $gameId));
        $villagers = array_filter($villagers, fn ($villager) => !in_array($villager, $werewolves, true));
        $aliveUsers = array_diff($game['users'], array_keys($game['dead_users']));

        if (
            array_key_exists('couple', $game) &&
            in_array($game['couple'][0], $aliveUsers, true)
        ) {
            return array_diff($aliveUsers, $game['couple']) !== [] &&
                array_diff($aliveUsers, $game['couple']) !== $this->getUserIdByRole(Role::Cupid, $gameId);
        }

        if (in_array(Role::WhiteWerewolf->value, array_keys($game['roles']), true)) {
            return !in_array($this->getUserIdByRole(Role::WhiteWerewolf, $gameId)[0], array_keys($game['dead_users']), true) &&
                count($werewolves) > 1 && count($villagers) >= 1;
        }

        if (in_array(Role::Parasite->value, array_keys($game['roles']), true)) {
            return !in_array($this->getUserIdByRole(Role::Parasite, $gameId)[0], array_keys($game['dead_users']), true) &&
                count($game['contaminated']) < (count(array_diff($game['users'], array_keys($game['dead_users']))) - 1);
        }

        return $villagers !== [] && $werewolves !== [];
    }

    private function getWinningUsers(string $gameId, Team|string $winningTeam): array
    {
        $game = $this->getGame($gameId);

        switch ($winningTeam) {
            case Team::Villagers:
                return $this->getUsersByTeam(Team::Villagers, $gameId);
            case Team::Werewolves:
                return $this->getUsersByTeam(Team::Werewolves, $gameId);
            case 'couple':
                return $game['couple'];
            case Team::Loners:
                if (
                    in_array(Role::Parasite->value, array_keys($game['roles']), true) &&
                    $this->alive($this->getUserIdByRole(Role::Parasite, $gameId)[0], $gameId) &&
                    count($game['contaminated']) === count(array_diff($game['users'], array_keys($game['dead_users']))) - 1
                ) {
                    return $this->getUserIdByRole(Role::Parasite, $gameId);
                } elseif (
                    in_array(Role::WhiteWerewolf->value, array_keys($game['roles']), true) &&
                    $this->alive($this->getUserIdByRole(Role::WhiteWerewolf, $gameId)[0], $gameId)
                ) {
                    return $this->getUserIdByRole(Role::WhiteWerewolf, $gameId);
                } elseif (
                    in_array(Role::Angel->value, array_keys($game['roles']), true) &&
                    $this->alive($this->getUserIdByRole(Role::Angel, $gameId)[0], $gameId)
                ) {
                    return $this->getUserIdByRole(Role::Angel, $gameId);
                }
        }

        return [];
    }

    private function getLoosingUsers(string $gameId, array $winners): array
    {
        $users = $this->getGame($gameId)['users'];

        return [...array_filter($users, fn ($user) => !in_array($user, $winners, true))];
    }

    /**
     * @param  string[]  $winners
     * @return array<string, array<string, string|int|array|null>>
     */
    private function getFormattedWinners(array $winners, string $gameId): array
    {
        $result = [];

        foreach ($winners as $winner) {
            $result[$winner] = $this->getRoleByUserId($winner, $gameId)->full();
        }

        return $result;
    }
}
