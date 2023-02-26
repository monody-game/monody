<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Services\EndGameService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EndGameController extends Controller
{
    public function __construct(
        private readonly EndGameService $service
    ) {
    }

    public function check(GameIdRequest $request): JsonResponse
    {
        if ($this->service->enoughTeamPlayersToContinue($request->validated('gameId'))) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function index(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');

        $this->service->end($gameId);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
