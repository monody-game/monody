<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

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

    public function add(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'game_id' => 'string|required',
            'user_id' => 'integer|required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (Redis::exists('game:' . $data['game_id'])) {
            /** @var string $game */
            $game = Redis::get('game:' . $data['game_id']);
            $game = json_decode($game, true);
            $game['users'][] = $data['user_id'];

            Redis::set('game:' . $data['game_id'], json_encode($game));

            return response()->json();
        }

        return response()->json(['error' => 'Game not found'], 404);
    }

    public function remove(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'game_id' => 'string|required',
            'user_id' => 'integer|required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (Redis::exists('game:' . $data['game_id'])) {
            /** @var string $game */
            $game = Redis::get('game:' . $data['game_id']);
            $game = json_decode($game, true);

            $index = array_search($data['user_id'], $game['users'], true);

            if (false !== $index) {
                unset($game['users'][$index]);
                Redis::set('game:' . $data['game_id'], json_encode($game));

                return response()->json();
            }

            return response()->json(['error' => 'User is not connected to the game'], 404);
        }

        return response()->json(['error' => 'Game not found'], 404);
    }
}
