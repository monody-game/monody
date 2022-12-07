<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Teams;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EndGameController extends Controller
{
    use MemberHelperTrait;

    public function check(GameIdRequest $request): JsonResponse
    {
        if ($this->enoughTeamPlayersToContinue($request->validated('gameId'))) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function index(): JsonResponse
    {
    }

    private function enoughTeamPlayersToContinue(string $gameId): bool
    {
        $villagers = $this->getUsersByTeam(Teams::Villagers, $gameId);
        $wereolves = $this->getUsersByTeam(Teams::Werewolves, $gameId);

        return $villagers !== [] && $wereolves !== [];
    }
}
