<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\GameType;
use App\Enums\Status;
use App\Enums\Team;
use App\Events\Bot\ClearGameInvitations;
use App\Events\Bot\UpdateVoiceChannelPermissions;
use App\Events\GameListUpdate;
use App\Events\WerewolvesList;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\JoinGameRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use App\Services\ListGamesService;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\Request;

class JoinGameController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function __construct(
        private readonly ListGamesService $listGamesService
    ) {
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

        $list = $this->listGamesService->list('*');

        broadcast(new GameListUpdate($list));

        broadcast(new ClearGameInvitations);

        if (($game['type'] & GameType::VOCAL->value) === GameType::VOCAL->value) {
            broadcast(new UpdateVoiceChannelPermissions([
                'game_id' => $gameId,
                'discord_id' => $user->discord_id ?? '',
                'join' => true,
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

        if (!$game) {
            return new JsonApiResponse(status: Status::NO_CONTENT);
        }

        if ($game['is_started'] === false) {
            // We remove the user id from saved users to prevent issues with roles that rely on the number of players
            $game['users'] = array_diff($game['users'], [$user->id]);
            Redis::set("game:$gameId", $game);
        }

        if (($game['type'] & GameType::VOCAL->value) === GameType::VOCAL->value) {
            broadcast(new UpdateVoiceChannelPermissions([
                'game_id' => $gameId,
                'discord_id' => $user->discord_id ?? '',
                'join' => false,
            ]));
        }

        $list = $this->listGamesService->list('*');

        broadcast(new GameListUpdate($list));

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }
}
