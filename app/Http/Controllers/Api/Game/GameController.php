<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\AlertType;
use App\Enums\States;
use App\Events\GameListUpdate;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\GameIdRequest;
use App\Http\Requests\JoinGameRequest;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\RegisterHelperTrait;
use function array_key_exists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    use RegisterHelperTrait, GameHelperTrait;

    public function check(Request $request): JsonResponse
    {
        if (!$request->has('gameId')) {
            return (new JsonResponse(null, Response::HTTP_BAD_REQUEST))
                ->withMessage('Please specify a game id to check');
        }

        /** @var bool $game */
        $game = Redis::exists("game:{$request->get('gameId')}");

        if ($game) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return (new JsonResponse(null, Response::HTTP_NOT_FOUND))
            ->withMessage('Game not found')
            ->withAlert(AlertType::Error, "La partie demandée n'existe pas (ou plus) ...");
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

            /** @var ?User $owner */
            $owner = User::find($gameData['owner']);

            if (!$owner) {
                continue;
            }

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
        $data['id'] = $id;

        if (!array_search($data['owner'], $data['users'], true)) {
            $user->current_game = $id;
            $user->save();
            $data['users'] = array_merge($data['users'], [$data['owner']]);
        }

        Redis::set("game:$id", $data);
        Redis::set("game:$id:state", [
            'status' => States::Waiting,
            'counterDuration' => States::Waiting->duration(),
        ]);
        Redis::set("game:$id:votes", []);

        $data['owner'] = [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];

        broadcast(new GameListUpdate($this->list()->getData(true)['games']));

        return new JsonResponse(['game' => $data]);
    }

    public function generateGameId(): string
    {
        return Str::random(12);
    }

    public function delete(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');

        $this->clearRedisKeys($gameId);

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
