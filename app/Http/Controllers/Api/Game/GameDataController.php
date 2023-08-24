<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\GameType;
use App\Enums\Role;
use App\Enums\State;
use App\Enums\Status;
use App\Enums\Team;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use App\Traits\MemberHelperTrait;

class GameDataController extends Controller
{
    use MemberHelperTrait;

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

        if (($game['type'] & GameType::VOCAL->value) === GameType::VOCAL->value) {
            $payload['discord'] = Redis::get("game:$gameId:discord");
        }

        if (
            array_key_exists('couple', $game) &&
            (
                in_array($userId, $game['couple'], true) ||
                $this->getRoleByUserId($userId, $gameId) === Role::Cupid
            )
        ) {
            $payload['couple'] = $game['couple'];
        }

        return new JsonApiResponse(['game' => $payload]);
    }
}
