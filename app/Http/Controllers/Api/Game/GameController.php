<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\GameStates;
use App\Events\GameCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckGameRequest;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\DeleteGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Models\Game;
use App\Models\User;
use App\Traits\RegisterHelperTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;

class GameController extends Controller
{
    use RegisterHelperTrait;

    public function check(CheckGameRequest $request): JsonResponse
    {
        /** @var array $data */
        $data = $request->validated();

        $game = Redis::get("game:{$data['game_id']}");

        if ($game) {
            return new JsonResponse(['message' => 'Game found']);
        }

        return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
    }

    public function list(): JsonResponse
    {
        $cursor = '0';
        /** @var string[] $games */
        /** @phpstan-ignore-next-line */
        $games = Redis::scan($cursor, ['MATCH' => 'game:*', 'COUNT' => 20])[1];
        $list = [];

        if (!$games) {
            return new JsonResponse(['games' => []]);
        }

        foreach ($games as $game) {
            if (!(bool) preg_match('/^game:[^:]+$/', $game)) {
                continue;
            }

            $gameData = Redis::get($game);

            if (!$gameData) {
                continue;
            }

            $currentGame = json_decode($gameData, true);

            if (!$currentGame) {
                continue;
            }

            if ($currentGame['is_started']) {
                continue;
            }

            /** @var User $owner */
            $owner = User::find($currentGame['owner']);

            $currentGame['owner'] = [
                'id' => $owner->id,
                'username' => $owner->username,
                'avatar' => $owner->avatar,
            ];

            $currentGame['id'] = str_replace('game:', '', $game);
            unset($currentGame['assigned_roles']);
            unset($currentGame['is_started']);

            $list[] = $currentGame;
        }

        return new JsonResponse(['games' => $list]);
    }

    public function new(CreateGameRequest $request): JsonResponse
    {
        $data = $request->validated();
        /** @var User $user */
        $user = $request->user();

        $data['users'] = array_key_exists('users', $data) ? $data['users'] : [];
        $data['roles'] = array_count_values($data['roles']);
        $data['assigned_roles'] = [];
        $data['owner'] = $user->id;
        $data['is_started'] = array_key_exists('is_started', $data) ? $data['is_started'] : false;
        $id = $this->generateGameId();

        if (!array_search($data['owner'], $data['users'], true)) {
            $user->current_game = $id;
            $user->save();
            $data['users'] = array_merge($data['users'], [$data['owner']]);
        }

        Redis::set("game:$id", json_encode($data));
        Redis::set("game:$id:state", json_encode([
            'state' => GameStates::WAITING_STATE->value,
            'duration' => GameStates::WAITING_STATE->duration()
        ]));
        Redis::set("game:$id:votes", json_encode([]));

        $data['id'] = $id;
        $data['owner'] = [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];

        broadcast(new GameCreated(new Game($data)));

        return new JsonResponse(['game' => $data]);
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
        $gameId = $request->validated('game_id');

        Redis::del("game:{$gameId}");
        Redis::del("game:{$gameId}:state");
        Redis::del("game:{$gameId}:members");
        Redis::del("game:{$gameId}:votes");

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function join(JoinGameRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = User::find($request->validated('userId'));
        $user->current_game = $request->validated('gameId');
        $user->save();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
