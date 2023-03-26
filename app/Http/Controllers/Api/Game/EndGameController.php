<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Http\Responses\JsonApiResponse;
use App\Services\EndGameService;

class EndGameController extends Controller
{
    public function __construct(
        private readonly EndGameService $service,
    ) {
    }

    public function check(GameIdRequest $request): JsonApiResponse
    {
        if ($this->service->enoughTeamPlayersToContinue($request->validated('gameId'))) {
            return new JsonApiResponse(status: Status::FORBIDDEN);
        }

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function index(GameIdRequest $request): JsonApiResponse
    {
        $gameId = $request->validated('gameId');

        $this->service->end($gameId);

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }
}
