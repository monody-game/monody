<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GameUsersController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!\array_key_exists('game_id', $data)) {
            return response()->json(['error' => 'Game id is required'], 400);
        }

        $game = Redis::get('game:' . $data['game_id']);

        if ($game) {
            $game = json_decode($game, true);

            return response()->json(['users' => $game['users']]);
        }

        return response()->json(['error' => 'Game not found'], 404);
    }

    public function leave(User $user, string $gameId): void
    {
        if (Redis::exists('game:' . $gameId)) {
            /** @var string $game */
            $game = Redis::get('game:' . $gameId);
            $game = json_decode($game, true);

            $index = array_search($user->id, $game['users'], true);

            if (false !== $index) {
                unset($game['users'][$index]);
                Redis::set('game:' . $gameId, json_encode($game));
            }
        }
    }
}
