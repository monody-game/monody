<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\AlertType;
use App\Enums\GameType;
use App\Enums\State;
use App\Enums\Team;
use App\Events\Bot\ClearSharedGames;
use App\Events\Bot\CreateVocalChannel;
use App\Events\GameListUpdate;
use App\Events\WerewolvesList;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\GameIdRequest;
use App\Http\Requests\JoinGameRequest;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;
use function array_key_exists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    use RegisterHelperTrait, GameHelperTrait, MemberHelperTrait;

    public function check(Request $request): JsonResponse
    {
        if (!$request->has('gameId')) {
            return (new JsonResponse(null, Response::HTTP_BAD_REQUEST))
                ->withMessage('Please specify a game id to check');
        }

        $game = Redis::exists("game:{$request->get('gameId')}");

        if ($game) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return (new JsonResponse(null, Response::HTTP_NOT_FOUND))
            ->withMessage('Game not found')
            ->withAlert(AlertType::Error, "La partie demandée n'existe pas (ou plus) ...");
    }

    public function list(?string $type = null): JsonResponse
    {
        $games = $this->getGames();
        $list = [];

        if ($games === []) {
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

            if ($this->fromLocalNetwork() && $gameData['type'] !== (int) $type && $type !== '*') {
                continue;
            }

            if ($type !== null && $gameData['type'] !== (int) $type && $type !== '*') {
                continue;
            }

            $owner = User::where('id', $gameData['owner'])->first();

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

        if ($request->get('type') === GameType::VOCAL && $user->discord_id === null) {
            return new JsonResponse([
                'message' => 'You must link your Discord account to Monody in order to create a vocal game.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $data['users'] = array_key_exists('users', $data) ? $data['users'] : [];
        $data['roles'] = array_count_values($data['roles']);
        $data['assigned_roles'] = [];
        $data['owner'] = $user->id;
        $data['is_started'] = false;
        $data['dead_users'] = [];
        $id = Str::random(12);
        $data['id'] = $id;
        $data['type'] = $request->get('type') ?: GameType::NORMAL;

        if ($data['type'] === GameType::VOCAL->value) {
            broadcast(new CreateVocalChannel(
                [
                    'game_id' => $id,
                    'owner' => [
                        'username' => $user->username,
                        'discord_id' => $user->discord_id,
                    ],
                ]
            ));
        }

        if (!array_search($data['owner'], $data['users'], true)) {
            $user->current_game = $id;
            $user->save();
            $data['users'] = array_merge($data['users'], [$data['owner']]);
        }

        $data['owner'] = [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];

        Redis::set("game:$id", $data);
        Redis::set("game:$id:state", [
            'status' => State::Waiting,
            'counterDuration' => State::Waiting->duration(),
            'round' => 0,
            'startTimestamp' => Carbon::now()->timestamp,
        ]);
        Redis::set("game:$id:votes", []);

        broadcast(new GameListUpdate($this->list()->getData(true)['games']));

        return new JsonResponse(['game' => $data]);
    }

    public function delete(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');

        $this->clearRedisKeys($gameId);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function join(JoinGameRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');
        $game = $this->getGame($gameId);
        /** @var User $user */
        $user = User::find($request->validated('userId'));
        $user->current_game = $gameId;
        $user->save();

        $werewolves = $this->getUsersByTeam(Team::Werewolves, $gameId);

        broadcast(
            new WerewolvesList(
                [
                    'gameId' => $gameId,
                    'list' => $werewolves,
                ],
                true,
                [...$werewolves, ...$game['dead_users']]
            )
        );

        broadcast(new ClearSharedGames);

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

    public function data(string $gameId): JsonResponse
    {
        if (!Redis::exists("game:$gameId")) {
            return new JsonResponse("Game $gameId not found.", Response::HTTP_NOT_FOUND);
        }

        $game = Redis::get("game:$gameId");
        /** @var User $owner */
        $owner = User::where('id', $game['owner']['id'])->first();
        $votes = Redis::get("game:$gameId:votes");
        $state = Redis::get("game:$gameId:state");
        $interactions = Redis::get("game:$gameId:interactions") ?? [];

        return new JsonResponse([
            'game' => [
                'id' => $gameId,
                'owner' => [
                    'id' => $owner->id,
                    'username' => $owner->username,
                    'avatar' => $owner->avatar,
                    'level' => $owner->level,
                    'elo' => 'N/A',
                ],
                'roles' => $game['roles'],
                'dead_users' => $game['dead_users'],
                'voted_users' => $votes,
                'state' => $state,
                'current_interactions' => $interactions,
            ],
        ]);
    }

    /**
     * @return array{}|string[]
     */
    private function getGames(): array
    {
        $cursor = 0;

        return Redis::scan($cursor, ['MATCH' => 'game:*', 'COUNT' => 20])[1];
    }
}
