<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function users(Request $request, int $id): JsonResponse
    {
        $users = DB::select(
            DB::raw(
                <<<SQL
    select `users`.`id`, `users`.`username`, `users`.`avatar` from `users`
    inner join `game_users` on `game_users`.`game_id` = :game_id
    where `users`.`id` = `game_users`.`user_id`
SQL
            ),
            [
                'game_id' => $id
            ]
        );

        return response()->json(['users' => $users]);
    }

    public function token(Request $request): JsonResponse
    {
        $key = env('JWT_SECRET');
        $user = $request->user();
        $token = [
            'user_id' => $user->id,
            'user_name' => $user->username,
            'user_avatar' => $user->avatar,
            'exp' => time() + 30
        ];

        $token = JWT::encode($token, $key);

        return response()->json(['token' => $token]);
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
