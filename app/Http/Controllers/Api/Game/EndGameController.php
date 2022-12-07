<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Teams;
use App\Events\GameLoose;
use App\Events\GameWin;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
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

    public function index(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');

        broadcast(new GameWin(['gameId' => $gameId], true, $this->getWinningUsers($gameId)));
        broadcast(new GameLoose(['gameId' => $gameId], true, $this->getLoosingUsers($gameId)));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function enoughTeamPlayersToContinue(string $gameId): bool
    {
        $villagers = $this->getUsersByTeam(Teams::Villagers, $gameId);
        $wereolves = $this->getUsersByTeam(Teams::Werewolves, $gameId);

        return $villagers !== [] && $wereolves !== [];
    }

    private function getWinningUsers(string $gameId): array
    {
        $villagers = $this->getUsersByTeam(Teams::Villagers, $gameId);
        $werewolves = $this->getUsersByTeam(Teams::Werewolves, $gameId);

        if ($werewolves === []) {
            return $villagers;
        }

        return $werewolves;
    }

    private function getLoosingUsers(string $gameId): array
    {
        $users = $this->getGame($gameId)['users'];
        $winners = $this->getWinningUsers($gameId);

        return [...array_filter($users, fn ($user) => !in_array($user, $winners, true))];
    }
}
