<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\GameStates;
use App\Http\Controllers\Controller;
use App\Http\Requests\AfterVoteRequest;
use App\Http\Requests\VoteRequest;
use App\Services\VoteService;
use App\Traits\GameHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GameVoteController extends Controller
{
    use GameHelperTrait;

    public function vote(VoteRequest $request): JsonResponse
    {
        $service = new VoteService();
        $gameId = $request->validated('gameId');

        if (!$this->isStarted($gameId)) {
            return new JsonResponse(['Wait the game to start before voting'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isVoteState($gameId)) {
            return new JsonResponse(['Wait your turn to vote'], Response::HTTP_FORBIDDEN);
        }

        $service->vote($request->validated('userId'), $gameId);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function afterVote(AfterVoteRequest $request): JsonResponse
    {
        $service = new VoteService();
        $gameId = $request->validated('gameId');

        if (!$this->isStarted($gameId)) {
            return new JsonResponse(['Game is not started'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $res = $service->afterVote($gameId, $this->getContext($gameId));

        if ($res) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(['Not any player to vote, or vote cancelled'], Response::HTTP_OK);
    }

    private function isStarted(string $gameId): bool
    {
        return $this->getGame($gameId)['is_started'];
    }

    private function isVoteState(string $gameId): bool
    {
        $state = $this->getState($gameId);

        return GameStates::Vote === GameStates::from($state['status']);
    }

    private function getContext(string $gameId): string
    {
        $state = $this->getState($gameId)['status'];
        $state = GameStates::from($state);

        return $state->stringify();
    }
}
