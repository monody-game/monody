<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GameUsersController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        if (!$request->has('game_id')) {
            return response()->json(['error' => 'Game id is required'], 400);
        }

        $id = $request->get('game_id');
        $game = Redis::get("game:$id");

        if ($game) {
            $game = json_decode($game, true);

            return response()->json(['users' => $game['users']]);
        }

        return response()->json(['error' => 'Game not found'], 404);
    }
}
