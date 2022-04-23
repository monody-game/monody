<?php

namespace App\Http\Controllers\Api;

use App\Events\GameCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckGameRequest;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\DeleteGameRequest;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class GameController extends Controller
{
    private const GAME_WAITING = 0;

    public function check(CheckGameRequest $request): JsonResponse
    {
        /** @var array $data */
        $data = $request->post();

        $game = Redis::get("game:{$data['game_id']}");

        if ($game) {
            return response()->json(['message' => 'Game found']);
        }

        return response()->json(['error' => 'Game not found'], 404);
    }

    public function list(): JsonResponse
    {
        $cursor = '0';
        /** @var array[] $games */
        /** @phpstan-ignore-next-line */
        $games = Redis::scan($cursor, ['MATCH' => 'game:*', 'COUNT' => 20]);
        $list = [];
        $user = new User();

        if ($games) {
            foreach ($games[1] as $game) {
                if (1 === preg_match('/^game:[^:]+$/', $game)) {
                    $gameData = Redis::get($game);
                    if ($gameData) {
                        $currentGame = json_decode($gameData, true);
                        if ($currentGame['is_started']) {
                            continue;
                        }
                        $owner = $user->find(['id' => $currentGame['owner']]);
                        if ($owner) {
                            $currentGame['owner'] = $owner->first();
                            $currentGame['id'] = str_replace('game:', '', $game);
                            unset($currentGame['assigned_roles']);
                            unset($currentGame['is_started']);
                            $list[] = $currentGame;
                        }
                    }
                }
            }
        }

        return response()->json(['games' => $list]);
    }

    public function new(CreateGameRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['users'] = \array_key_exists('users', $data) ? $data['users'] : [];
        $data['roles'] = array_count_values($data['roles']);
        $data['assigned_roles'] = [];
        $data['owner'] = $request->user()?->id;
        $data['is_started'] = false;
        $id = $this->generateGameId();

        if (!array_search($data['owner'], $data['users'], true)) {
            $data['users'] = array_merge($data['users'], [$data['owner']]);
        }

        Redis::set("game:{$id}", json_encode($data));
        Redis::set("game:{$id}:state", self::GAME_WAITING);

        $data['id'] = $id;
        $data['owner'] = $request->user();

        broadcast(new GameCreated(new Game($data)));

        return response()->json(['game' => $data]);
    }

    public function generateGameId(): string
    {
        $bytes = random_bytes(10);
        $id = base64_encode($bytes);
        $id = str_replace(['+', '/', '='], '', $id);

        if (Redis::exists("game:{$id}")) {
            return $this->generateGameId();
        }

        return $id;
    }

    public function delete(DeleteGameRequest $request): JsonResponse
    {
        /** @var string $gameId */
        $gameId = $request->post('game_id');

        Redis::del("game:{$gameId}");
        Redis::del("game:{$gameId}:state");

        return response()->json();
    }
}
