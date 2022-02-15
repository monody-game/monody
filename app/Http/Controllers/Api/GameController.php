<?php

namespace App\Http\Controllers\Api;

use App\Events\GameCreated;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $data = $request->all();

        if (!isset($data['gameId'])) {
            return response()->json(['error' => 'Game id is required'], 400);
        }

        $game = Redis::get('game:' . $data['gameId']);

        if ($game) {
            return response()->json(['message' => 'Game found']);
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
        $games = Redis::scan('0', 'COUNT', '100', 'MATCH', 'game:*');
        $list = [];
        $user = new User();

        if($games[1]) {
            foreach ($games[1] as $game) {
                $currentGame = json_decode(Redis::get($game), true);
                $currentGame['owner'] = $user->find(['id' => $currentGame['owner']])[0];
                $currentGame['id'] = str_replace('game:', '', $game);
                $list[] = $currentGame;
            }
        }

        return response()->json(['games' => $list]);
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
        $data['roles'] = array_count_values($data['roles']);
        $data['owner'] = $request->user()->id;
        $data['is_started'] = \array_key_exists('is_started', $data) && (bool) $data['is_started'];
        $id = $this->generateGameId();

        if (!array_search($data['owner'], $data['users'], true)) {
            $data['users'] = array_merge($data['users'], [$data['owner']]);
        }

        Redis::set('game:' . $id, json_encode($data));

        $data['id'] = $id;

        broadcast(new GameCreated(new Game($data)));

        return response()->json(['game' => $data]);
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
}
