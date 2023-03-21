<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Role;
use App\Events\CloseVoiceChannelNotice;
use App\Events\Websockets\GameStart;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserJoinedVocalChannelRequest;
use App\Http\Requests\UserRoleRequest;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GameUsersController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function joined(UserJoinedVocalChannelRequest $request): JsonResponse
    {
        $query = User::where('discord_id', $request->validated('discord_id'))->get();
        /** @var User $user */
        $user = $query->first();
        $gameId = $user->current_game;

        if (!$gameId) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $discordData = Redis::get("game:$gameId:discord");
        $discordData['members'][$request->validated('discord_id')] = $user->id;

        Redis::set("game:$gameId:discord", $discordData);

        broadcast(new CloseVoiceChannelNotice($gameId, true, [$user->id]));

        $game = Redis::get("game:$gameId");

        if (StartGameController::isFull($game) && StartGameController::allUsersJoinedVoiceChannel($game)) {
            broadcast(new GameStart($game));
        }

        return new JsonResponse();
    }

    public function list(string $gameId): JsonResponse
    {
        $game = $this->getGame($gameId);

        return new JsonResponse(['users' => $game['users']]);
    }

    public function role(UserRoleRequest $request): JsonResponse
    {
        /** @var string $gameId */
        $gameId = $request->user()?->current_game;

        /** @var string[] $game */
        $game = $this->getGame($gameId);

        $userRole = $game['assigned_roles'][$request->validated('id')];
        $role = Role::from($userRole)->full();

        return new JsonResponse($role);
    }
}
