<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!\array_key_exists('id', $data)) {
            return response()->json(['error' => 'Game id is required'], 400);
        }

        $game = Redis::get('game:' . $data['id']);

        if ($game) {
            $game = json_decode($game, true);

            return response()->json(['users' => $game['users']]);
        }

        return response()->json(['error' => 'Game not found'], 404);
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
        return response()->json(['games' => Redis::keys('game:*')]);
    }

    public function new(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'users' => 'array',
            'roles' => 'array|required',
            'is_started' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data['users'] = \array_key_exists('users', $data) ? $data['users'] : [];
        $data['owner_id'] = $request->user()->id;
        $data['is_started'] = \array_key_exists('is_started', $data) ? (bool) $data['is_started'] : false;
        $id = $this->generateGameId();

        if (!array_search($data['owner_id'], $data['users'], true)) {
            $data['users'] = array_merge($data['users'], [$data['owner_id']]);
        }

        Redis::set('game:' . $id, json_encode($data));

        $data['id'] = $id;

        return response()->json(['game' => $data]);
    }

    public function delete(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'game_id' => 'string|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        Redis::del('game:' . $data['game_id']);

        return response()->json();
    }

    private function generateGameId(): string
    {
        $bytes = random_bytes(10);
        $id = base64_encode($bytes);
        $id = str_replace(['+', '/', '='], '', $id);

        if (Redis::exists('game:' . $id)) {
            return $this->generateGameId();
        }

        return $id;
    }
}
