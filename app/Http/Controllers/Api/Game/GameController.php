<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\AlertType;
use App\Enums\GameType;
use App\Enums\Role;
use App\Enums\State;
use App\Enums\Status;
use App\Enums\Team;
use App\Events\Bot\ClearGameInvitations;
use App\Events\Bot\ClearVoiceChannels;
use App\Events\Bot\CreateVoiceChannel;
use App\Events\Bot\UpdateVoiceChannelPermissions;
use App\Events\GameListUpdate;
use App\Events\WerewolvesList;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\GameIdRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\Elo;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;
use function array_key_exists;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GameController extends Controller
{
    use RegisterHelperTrait, GameHelperTrait, MemberHelperTrait;

    public function check(Request $request): JsonApiResponse
    {
        if (!$request->has('gameId')) {
            return new JsonApiResponse(['gameId' => 'Field required.'], Status::UNPROCESSABLE_ENTITY);
        }

        $game = Redis::exists("game:{$request->get('gameId')}");

        if ($game) {
            return new JsonApiResponse(status: Status::NO_CONTENT);
        }

        return JsonApiResponse::make(['message' => "Game {$request->get('gameId')} not found"], Status::NOT_FOUND)
            ->withAlert(AlertType::Error, "La partie demandée n'existe pas (ou plus) ...");
    }

    public function list(?string $type = '*'): JsonApiResponse
    {
        $games = $this->getGames();
        $list = [];

        if ($games === []) {
            return JsonApiResponse::make(['games' => []])->withoutCache();
        }

        foreach ($games as $game) {
            if (!(bool) preg_match('/^game:[^:]+$/', $game)) {
                continue;
            }

            $gameData = Redis::get($game);

            if (!$gameData || !is_array($gameData)) {
                continue;
            }

            if ($gameData['is_started']) {
                continue;
            }

            if (count($gameData['users']) === 0) {
                continue;
            }

            if ($type !== '*' && $this->fromLocalNetwork() && !($gameData['type'] & (int) decbin((int) $type))) {
                continue;
            }

            if ($type !== '*' && $type !== null && !($gameData['type'] & (int) decbin((int) $type))) {
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

        return JsonApiResponse::make(data: ['games' => $list])->withoutCache();
    }

    public function new(CreateGameRequest $request): JsonApiResponse
    {
        $data = $request->validated();
        /** @var User $user */
        $user = $request->user();

        if ($request->get('type') & GameType::VOCAL->value && ($user->discord_id === null || $user->discord_linked_at === null)) {
            return new JsonApiResponse([
                'message' => 'You must link your Discord account to Monody in order to create a vocal game.',
            ], Status::BAD_REQUEST);
        }

        $data['users'] = array_key_exists('users', $data) ? $data['users'] : [];
        $data['roles'] = array_count_values($data['roles']);
        $data['assigned_roles'] = [];
        $data['owner'] = $user->id;
        $data['is_started'] = false;
        $data['dead_users'] = [];
        $id = Str::random(12);
        $data['id'] = $id;
        $data['type'] = $request->get('type') ?: GameType::NORMAL->value;

        if (GameType::VOCAL->value & $data['type']) {
            $size = array_reduce($data['roles'], fn ($previous, $role) => $previous + $role, 0);

            broadcast(new CreateVoiceChannel(
                [
                    'game_id' => $id,
                    'owner' => [
                        'username' => $user->username,
                        'discord_id' => $user->discord_id,
                    ],
                    'size' => $size,
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

        return JsonApiResponse::make(['game' => $data])->withoutCache();
    }

    public function delete(GameIdRequest $request): JsonApiResponse
    {
        $gameId = $request->validated('gameId');

        $shared = Redis::get('bot:game:shared') ?? [];
        unset($shared[$gameId]);
        Redis::set('bot:game:shared', $shared);
        broadcast(new ClearGameInvitations);

        $game = Redis::get("game:$gameId");

        if ($game['type'] & GameType::VOCAL->value) {
            $discordData = Redis::get("game:$gameId:discord");

            if ($discordData) {
                broadcast(new ClearVoiceChannels([
                    'channel_id' => $discordData['voice_channel'],
                    'game_id' => $gameId,
                ]));
            }
        }

        $this->clearRedisKeys($gameId);

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function join(JoinGameRequest $request): JsonApiResponse
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
                [...$werewolves, ...array_keys($game['dead_users'])]
            )
        );

        /** @var array $list */
        $list = $this->list()->data;

        broadcast(new GameListUpdate($list['games']));

        broadcast(new ClearGameInvitations);

        if ($game['type'] & GameType::VOCAL->value) {
            broadcast(new UpdateVoiceChannelPermissions([
                'game_id' => $gameId,
                'discord_id' => $user->discord_id ?? '',
            ]));
        }

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function leave(Request $request): JsonApiResponse
    {
        if (!$request->has('userId')) {
            return new JsonApiResponse(['userId' => 'Field required'], Status::UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = User::find($request->post('userId'));

        /** @var string $gameId */
        $gameId = $user->current_game;

        $user->current_game = null;
        $user->save();

        $game = Redis::get("game:$gameId");

        if ($game['is_started'] === false) {
            // We remove the user id from saved users to prevent issues with roles that rely on the number of players
            $game['users'] = array_diff($game['users'], [$user->id]);
            Redis::set("game:$gameId", $game);
        }

        /** @var array $list */
        $list = $this->list()->data;

        broadcast(new GameListUpdate($list['games']));

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function data(string $gameId, string $userId): JsonApiResponse
    {
        if (!Redis::exists("game:$gameId")) {
            return new JsonApiResponse(['message' => "Game $gameId not found."], Status::NOT_FOUND);
        }

        $game = Redis::get("game:$gameId");
        /** @var User $owner */
        $owner = User::where('id', $game['owner']['id'])->first();
        $votes = Redis::get("game:$gameId:votes");
        $state = Redis::get("game:$gameId:state");
        $interactions = Redis::get("game:$gameId:interactions") ?? [];

        $currentState = State::from($state['status']);
        $role = array_key_exists($userId, $game['assigned_roles']) ? $game['assigned_roles'][$userId] : null;
        $chatLocked = $currentState->background() === 'night' || ($role !== null && Team::role(Role::from($role)) === Team::Werewolves && $currentState === State::Werewolf);
        $contaminated = array_key_exists('contaminated', $game) && (in_array($userId, $game['contaminated'], true) || Role::from($role) === Role::Parasite) ? $game['contaminated'] : [];

        $payload = [
            'id' => $gameId,
            'owner' => [
                'id' => $owner->id,
                'username' => $owner->username,
                'avatar' => $owner->avatar,
                'level' => $owner->level,
                'elo' => Elo::select('elo')->where('user_id', $owner->id)->firstOrCreate(['user_id' => $owner->id])->elo,
            ],
            'roles' => $game['roles'],
            'role' => array_key_exists($owner->id, $game['assigned_roles']) ? Role::from($game['assigned_roles'][$userId])->full() : null,
            'dead_users' => array_keys($game['dead_users']),
            'voted_users' => $votes,
            'state' => $state,
            'current_interactions' => $interactions,
            'type' => $game['type'],
            'chat_locked' => $chatLocked,
            'contaminated' => $contaminated,
            'mayor' => array_key_exists('mayor', $game) ? $game['mayor'] : null,
        ];

        if ($game['type'] & GameType::VOCAL->value) {
            $payload['discord'] = Redis::get("game:$gameId:discord");
        }

        return new JsonApiResponse(['game' => $payload]);
    }

    public function discord(string $gameId): JsonApiResponse
    {
        return JsonApiResponse::make([
            'data' => Redis::get("game:$gameId:discord"),
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
