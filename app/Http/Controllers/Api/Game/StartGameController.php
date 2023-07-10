<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\GameType;
use App\Enums\Status;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Http\Responses\JsonApiResponse;

class StartGameController extends Controller
{
    public function check(GameIdRequest $request): JsonApiResponse
    {
        $gameId = $request->validated('gameId');
        $game = Redis::get("game:$gameId");

        if ($game['is_started'] === true) {
            return new JsonApiResponse(['message' => 'You cannot start an already started game.'], Status::FORBIDDEN);
        }

        if ($game['type'] & GameType::NORMAL->value && $this->isFull($game)) {
            return new JsonApiResponse(status: Status::NO_CONTENT);
        }

        if ($game['type'] & GameType::VOCAL->value && $this->isFull($game) && $this->allUsersJoinedVoiceChannel($game)) {
            return new JsonApiResponse(status: Status::NO_CONTENT);
        }

        return new JsonApiResponse(['message' => "Game $gameId is not ready to be started."], Status::FORBIDDEN);
    }

    public static function isFull(array $game): bool
    {
        $size = array_reduce($game['roles'], fn ($previous, $role) => $previous + $role, 0);

        return $size === count($game['users']);
    }

    public static function allUsersJoinedVoiceChannel(array $game): bool
    {
        $discordData = Redis::get("game:{$game['id']}:discord");

        return count($discordData['members']) === count($game['users']);
    }
}
