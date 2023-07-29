<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Role;
use App\Enums\Status;
use App\Events\CloseVoiceChannelNotice;
use App\Events\GameKill;
use App\Events\Websockets\GameStart;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserJoinedVocalChannelRequest;
use App\Http\Requests\UserRoleRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\Request;

class GameUsersController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function joined(UserJoinedVocalChannelRequest $request): JsonApiResponse
    {
        $query = User::where('discord_id', $request->validated('discord_id'))->get();
        /** @var User $user */
        $user = $query->first();
        $gameId = $user->current_game;

        if (!$gameId) {
            return new JsonApiResponse(status: Status::BAD_REQUEST);
        }

        Redis::update("game:$gameId:discord", fn (array &$discordData) => $discordData['members'][$request->validated('discord_id')] = $user->id);

        broadcast(new CloseVoiceChannelNotice($gameId, true, [$user->id]));

        $game = Redis::get("game:$gameId");

        if (StartGameController::isFull($game) && StartGameController::allUsersJoinedVoiceChannel($game)) {
            broadcast(new GameStart($game));
        }

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function list(string $gameId): JsonApiResponse
    {
        $game = $this->getGame($gameId);

        return JsonApiResponse::make(['users' => $game['users']])->withoutCache();
    }

    public function role(UserRoleRequest $request, string $gameId): JsonApiResponse
    {
        $game = $this->getGame($gameId);

        if (
            $this->alive($request->input('id'), $gameId) &&
            (array_key_exists('ended', $game) && $game['ended'] === false)
        ) {
            return new JsonApiResponse(data: ['message' => 'Player is alive'], status: Status::BAD_REQUEST);
        }

        $userRole = $game['assigned_roles'][$request->validated('id')];
        $role = Role::from($userRole)->full();

        return new JsonApiResponse(['role' => $role]);
    }

    public function eliminate(Request $request): JsonApiResponse
    {
        $gameId = $request->input('gameId');
        $userId = $request->input('userId');

        $res = $this->kill($userId, $gameId, $request->input('context'), false);
        $status = $res ? Status::NO_CONTENT : Status::BAD_REQUEST;

        if ($request->input('instant') === true) {
            $game = Redis::get("game:$gameId");
            $deaths = Redis::get("game:$gameId:deaths") ?? [];

            foreach ($deaths as $death) {
                if ($death['user'] !== $userId) {
                    continue;
                }

                $infected = array_key_exists('infected', $game) && $game['infected'] === $death['user'];

                GameKill::broadcast([
                    'killedUser' => $death['user'],
                    'gameId' => $gameId,
                    'context' => $death['context'],
                    'infected' => $infected,
                ]);

                Redis::update("game:$gameId:deaths", fn (array $deaths) => array_filter($deaths, fn ($storedDeath) => $storedDeath !== $death));

                break;
            }
        }

        return new JsonApiResponse(status: $status);
    }
}
