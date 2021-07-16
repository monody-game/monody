<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function token(Request $request): JsonResponse
    {
        $key = env('JWT_TOKEN');
        $user = $request->user();
        $token = [
            'user_id'     => $user->getId(),
            'user_name'   => $user->getUsername(),
            'user_avatar' => $user->getAvatar(),
            'exp'         => time() + 30
        ];
        dd($token);
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
        
        if($validator->fails()){
            return response()->json(['error' => $validator->errors(), 'Validation Error']);
        }
        
        $data['owner_id'] = 1;

        $game = Game::create($data);

        return response()->json(['message' => 'Game created !', 'game' => $game]);
    }
}
