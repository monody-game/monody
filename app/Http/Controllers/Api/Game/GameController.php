<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\States;
use App\Events\GameCreated;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckGameRequest;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\DeleteGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Models\Game;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\RegisterHelperTrait;
use function array_key_exists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    use RegisterHelperTrait, GameHelperTrait;

    public function check(CheckGameRequest $request): JsonResponse
    {
        /** @var array $data */
        $data = $request->validated();

        /** @var bool $game */
        $game = Redis::exists("game:{$data['game_id']}");

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

            if ($gameData['is_started']) {
                continue;
            }

            /** @var User $owner */
            $owner = User::find($gameData['owner']);

            $gameData['owner'] = [
                'id' => $owner->id,
                'username' => $owner->username,
                'avatar' => $owner->avatar,
            ];

            $gameData['id'] = str_replace('game:', '', $game);
            unset($gameData['assigned_roles']);
            unset($gameData['is_started']);

            $list[] = $gameData;
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

        Redis::set("game:$id", $data);
        Redis::set("game:$id:state", [
            'state' => States::Waiting,
            'duration' => States::Waiting->duration(),
        ]);
        Redis::set("game:$id:votes", []);

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

        Redis::del("game:{$gameId}", "game:{$gameId}:state", "game:{$gameId}:members", "game:{$gameId}:votes");

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

    public function leave(Request $request): JsonResponse
    {
        if (!$request->has('userId')) {
            return new JsonResponse(['userId' => 'Field required'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = User::find($request->post('userId'));
        $user->current_game = null;
        $user->save();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
