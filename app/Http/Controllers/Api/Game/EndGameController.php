<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Events\GameEnd;
use App\Events\GameLoose;
use App\Events\GameWin;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Models\GameOutcome;
use App\Models\Statistic;
use App\Models\User;
use App\Services\ExpService;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EndGameController extends Controller
{
    use MemberHelperTrait, GameHelperTrait;

    public function check(GameIdRequest $request): JsonResponse
    {
        if ($this->enoughTeamPlayersToContinue($request->validated('gameId'))) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function index(GameIdRequest $request, ExpService $expService): JsonResponse
    {
        $gameId = $request->validated('gameId');
        $winners = $this->getWinningUsers($gameId);
        $loosers = $this->getLoosingUsers($gameId);
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
                $expService->add(50, $user);
                $stat->win_streak++;

                if ($stat->win_streak > $stat->longest_streak) {
                    $stat->longest_streak = $stat->win_streak;
                }
            } else {
                $expService->add(20, $user);
                $stat->win_streak = 0;
            }

            $stat->save();

            $outcome = new GameOutcome();
            $outcome->user_id = $userId;
            $outcome->role_id = $this->getRoleByUserId($userId, $gameId)->value;
            $outcome->win = $win;
            $outcome->save();

            if ($userId === $game['owner']) {
                $expService->add(20, $user->refresh());
            }
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function getWinningTeam(string $gameId): Teams
    {
        $werewolves = $this->getUsersByTeam(Teams::Werewolves, $gameId);

        if ($werewolves === []) {
            return Teams::Villagers;
        }

        if (
            $werewolves === $this->getUserIdByRole(Roles::WhiteWerewolf, $gameId)
        ) {
            return Teams::Loners;
        }

        return Teams::Werewolves;
    }

    private function enoughTeamPlayersToContinue(string $gameId): bool
    {
        $game = $this->getGame($gameId);
        $villagers = $this->getUsersByTeam(Teams::Villagers, $gameId);
        $werewolves = array_filter($game['werewolves'], fn ($werewolf) => $this->alive($werewolf, $gameId));
        $villagers = array_filter($villagers, fn ($villager) => !in_array($villager, $werewolves, true));
        $whiteWerewolf = false;

        if (in_array(Roles::WhiteWerewolf->value, array_keys($game['roles']), true)) {
            $whiteWerewolf = !in_array($this->getUserIdByRole(Roles::WhiteWerewolf, $gameId)[0], $game['dead_users'], true) && count($werewolves) > 1;
        }

        return ($villagers !== [] && $werewolves !== []) || $whiteWerewolf;
    }

    private function getWinningUsers(string $gameId): array
    {
        $game = $this->getGame($gameId);
        $villagers = $this->getUsersByTeam(Teams::Villagers, $gameId);
        $werewolves = array_filter($game['werewolves'], fn ($werewolf) => $this->alive($werewolf, $gameId));
        $villagers = array_filter($villagers, fn ($villager) => !in_array($villager, $werewolves, true));

        if ($werewolves === []) {
            return $villagers;
        }

        return [...$werewolves];
    }

    private function getLoosingUsers(string $gameId): array
    {
        $users = $this->getGame($gameId)['users'];
        $winners = $this->getWinningUsers($gameId);

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
