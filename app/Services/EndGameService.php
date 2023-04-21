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
        private readonly BadgeService $badgeService,
        private readonly EloService $eloService
    ) {
    }

    /**
     * @param  string[]|null  $winners
     */
    public function end(string $gameId, ?array $winners = null): void
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

        $game = Redis::get("game:$gameId");
        $game['ended'] = true;
        Redis::set("game:$gameId", $game);

        foreach ([...$winners, ...$loosers] as $userId) {
            $win = in_array($userId, $winners, true);
            $stat = Statistic::firstOrCreate(['user_id' => $userId]);

            $outcome = new GameOutcome();
            $outcome->user_id = $userId;
            $outcome->role_id = $this->getRoleByUserId($userId, $gameId)->value;
            $outcome->win = $win;
            $outcome->save();

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

            $elo = $this->eloService->computeElo($user, $gameId, $win);
            $this->eloService->add($elo, $user);

            if ($userId === $game['owner']['id']) {
                $this->expService->add(20, $user->refresh());
            }
        }

        broadcast(new ClearGameInvitations);
    }

    private function getWinningTeam(string $gameId): Team|string
    {
        $game = Redis::get("game:$gameId");
        $werewolves = $this->getUsersByTeam(Team::Werewolves, $gameId);
        $aliveUsers = array_diff($game['users'], $game['dead_users']);
        $couple = array_key_exists('couple', $game) ? $game['couple'] : [];

        sort($aliveUsers);
        sort($couple);

        if (
            array_key_exists('couple', $game) &&
            $aliveUsers === $couple
        ) {
            return 'couple';
        }

        if ($werewolves === []) {
            return Team::Villagers;
        }

        if (
            $werewolves === $this->getUserIdByRole(Role::WhiteWerewolf, $gameId) ||
            in_array(Role::Parasite->value, array_keys($game['roles']), true) &&
            $this->alive($this->getUserIdByRole(Role::Parasite, $gameId)[0], $gameId)
        ) {
            return Team::Loners;
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
        $aliveUsers = array_diff($game['users'], $game['dead_users']);

        if (
            array_key_exists('couple', $game) &&
            in_array($game['couple'][0], $aliveUsers, true)
        ) {
            return array_diff($aliveUsers, $game['couple']) !== [] &&
                array_diff($aliveUsers, $game['couple']) !== $this->getUserIdByRole(Role::Cupid, $gameId);
        }

        if (in_array(Role::WhiteWerewolf->value, array_keys($game['roles']), true)) {
            return !in_array($this->getUserIdByRole(Role::WhiteWerewolf, $gameId)[0], $game['dead_users'], true) &&
                count($werewolves) > 1;
        }

        if (in_array(Role::Parasite->value, array_keys($game['roles']), true)) {
            return !in_array($this->getUserIdByRole(Role::Parasite, $gameId)[0], $game['dead_users'], true) &&
                count($game['contaminated']) < (count(array_diff($game['users'], $game['dead_users'])) - 1);
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
                    count($game['contaminated']) === count(array_diff($game['users'], $game['dead_users'])) - 1
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
