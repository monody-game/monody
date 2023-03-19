<?php

namespace App\Services;

use App\Enums\Badge;
use App\Enums\Role;
use App\Enums\Team;
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
    ) {
    }

    /**
     * @param  string[]|null  $winners
     */
    public function end(string $gameId, ?array $winners = null): void
    {
        $winners = $winners ?? $this->getWinningUsers($gameId);
        $loosers = $this->getLoosingUsers($gameId, $winners);
        $payload = [
            'gameId' => $gameId,
        ];

        broadcast(new GameEnd(array_merge($payload, [
            'winners' => $this->getFormattedWinners($winners, $gameId),
            'winningTeam' => $this->getWinningTeam($gameId),
        ])));
        broadcast(new GameWin($payload, true, $winners));
        broadcast(new GameLoose($payload, true, $loosers));

        $game = Redis::get("game:$gameId");
        $game['ended'] = true;
        Redis::set("game:$gameId", $game);

        foreach ([...$winners, ...$loosers] as $userId) {
            $win = in_array($userId, $winners, true);
            $stat = Statistic::firstOrCreate(['user_id' => $userId]);

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

            $outcome = new GameOutcome();
            $outcome->user_id = $userId;
            $outcome->role_id = $this->getRoleByUserId($userId, $gameId)->value;
            $outcome->win = $win;
            $outcome->save();

            if ($userId === $game['owner']['id']) {
                $this->expService->add(20, $user->refresh());
            }
        }
    }

    private function getWinningTeam(string $gameId): Team
    {
        $werewolves = $this->getUsersByTeam(Team::Werewolves, $gameId);

        if ($werewolves === []) {
            return Team::Villagers;
        }

        if (
            $werewolves === $this->getUserIdByRole(Role::WhiteWerewolf, $gameId)
        ) {
            return Team::Loners;
        }

        return Team::Werewolves;
    }

    public function enoughTeamPlayersToContinue(string $gameId): bool
    {
        $game = $this->getGame($gameId);
        $villagers = $this->getUsersByTeam(Team::Villagers, $gameId);
        $werewolves = array_filter($game['werewolves'], fn ($werewolf) => $this->alive($werewolf, $gameId));
        $villagers = array_filter($villagers, fn ($villager) => !in_array($villager, $werewolves, true));
        $whiteWerewolf = false;

        if (in_array(Role::WhiteWerewolf->value, array_keys($game['roles']), true)) {
            $whiteWerewolf = !in_array($this->getUserIdByRole(Role::WhiteWerewolf, $gameId)[0], $game['dead_users'], true) && count($werewolves) > 1;
        }

        return ($villagers !== [] && $werewolves !== []) || $whiteWerewolf;
    }

    private function getWinningUsers(string $gameId): array
    {
        $game = $this->getGame($gameId);
        $villagers = $this->getUsersByTeam(Team::Villagers, $gameId);
        $werewolves = array_filter($game['werewolves'], fn ($werewolf) => $this->alive($werewolf, $gameId));
        $villagers = array_filter($villagers, fn ($villager) => !in_array($villager, $werewolves, true));

        if ($werewolves === []) {
            return $villagers;
        }

        return [...$werewolves];
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
