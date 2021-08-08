<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function token(Request $request): JsonResponse
    {
        $key = env('JWT_TOKEN');
        $user = $request->user();
        $token = [
            'user_id' => $user->getId(),
            'user_name' => $user->getUsername(),
            'user_avatar' => $user->getAvatar(),
            'exp' => time() + 30
        ];
        $token = JWT::encode($token, $key);

        return new JsonResponse(['token' => $token]);
    }

    public function list(Request $request): JsonResponse
    {
        $games = Game::limit(25)->get();

        return response()->json(['games' => $games, 'message' => 'Listed successfully']);
    }

    public function new(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'users' => 'json|required',
            'roles' => 'json|required',
            'is_started' => 'boolean|required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 400);
        }

        $data['owner_id'] = $request->user()->id;

        $game = Game::create($data);

        return response()->json(['message' => 'Game created !', 'game' => $game]);
    }

    public function delete(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'game_id' => 'integer|required|exists:games,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 400);
        }

        Game::destroy($data['game_id']);

        return response()->json(['message' => 'Game successfully deleted']);
    }
}
