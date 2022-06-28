<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\AfterVoteRequest;
use App\Http\Requests\VoteRequest;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class GameVoteController extends Controller
{
    public const GAME_VOTE_STATE = 5;

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

        $res = $service->afterVote($gameId);

        if ($res) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(['Not any player to vote, or vote cancelled'], Response::HTTP_OK);
    }

    private function getGame(string $id): array
    {
        $game = Redis::get("game:{$id}");

        return json_decode($game, true);
    }

    private function isStarted(string $gameId): bool
    {
        return $this->getGame($gameId)['is_started'];
    }

    private function isVoteState(string $gameId): bool
    {
        /** @var string $state */
        $state = Redis::get("game:{$gameId}:state");
        $state = json_decode($state, true);

        return self::GAME_VOTE_STATE === $state['status'];
    }
}
