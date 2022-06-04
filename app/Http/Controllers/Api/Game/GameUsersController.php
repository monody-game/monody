<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class GameUsersController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        if (!$request->has('game_id')) {
            return new JsonResponse(['error' => 'Game id is required'], Response::HTTP_BAD_REQUEST);
        }

        $id = $request->get('game_id');
        $game = Redis::get("game:$id");

        if ($game) {
            $game = json_decode($game, true);

            return new JsonResponse(['users' => $game['users']]);
        }

        return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
    }
}
