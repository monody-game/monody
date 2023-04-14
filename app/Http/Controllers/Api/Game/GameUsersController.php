<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Role;
use App\Enums\Status;
use App\Events\CloseVoiceChannelNotice;
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

        $discordData = Redis::get("game:$gameId:discord");
        $discordData['members'][$request->validated('discord_id')] = $user->id;

        Redis::set("game:$gameId:discord", $discordData);

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

        return new JsonApiResponse(['users' => $game['users']]);
    }

    public function role(UserRoleRequest $request): JsonApiResponse
    {
        /** @var string $gameId */
        $gameId = $request->user()?->current_game;

        /** @var string[] $game */
        $game = $this->getGame($gameId);

        $userRole = $game['assigned_roles'][$request->validated('id')];
        $role = Role::from($userRole)->full();

        return new JsonApiResponse(['role' => $role]);
    }

    public function eliminate(Request $request): JsonApiResponse
    {
        $res = $this->kill($request->get('userId'), $request->get('gameId'), $request->get('context'));
        $status = $res ? Status::NO_CONTENT : Status::BAD_REQUEST;

        return new JsonApiResponse(status: $status);
    }
}
