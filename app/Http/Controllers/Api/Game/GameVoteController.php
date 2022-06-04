<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoteRequest;
use App\VoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;

class GameVoteController extends Controller
{
    public const GAME_VOTE_STATE = 5;

    public function vote(VoteRequest $request): JsonResponse
    {
        $service = new VoteService();
        $gameId = $request->validated('gameId');
        $response = Gate::inspect('vote', $gameId);

        if (!$response->allowed()) {
            return new JsonResponse([$response->message()], 403);
        }

        if (!$this->isStarted($gameId)) {
            return new JsonResponse(['Wait the game to start before voting'], 403);
        }

        if (!$this->isVoteState($gameId)) {
            return new JsonResponse(['Wait your turn to vote'], 403);
        }

        $service->vote($request->validated('userId'), $gameId);

        return new JsonResponse([], 204);
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
        return self::GAME_VOTE_STATE === $this->getGame($gameId)['state'];
    }
}
