<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Status;
use App\Enums\Team;
use App\Events\WerewolvesList;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Http\Responses\JsonApiResponse;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;

class RoleController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function all(): JsonApiResponse
    {
        return new JsonApiResponse(['roles' => Role::all()]);
    }

    public function game(string $gameId): JsonApiResponse
    {
        $game = $this->getGame($gameId);
        $roles = array_map(fn ($role) => Role::from($role)->full(), array_keys($game['roles']));

        return new JsonApiResponse(['roles' => $roles]);
    }

    public function get(int $id): JsonApiResponse
    {
        $role = Role::tryFrom($id);

        if ($role !== null) {
            return new JsonApiResponse(['role' => $role->full()]);
        }

        return new JsonApiResponse(['message' => "Role with id $id not found."], Status::NOT_FOUND);
    }

    public function group(int $group): JsonApiResponse
    {
        $roles = Team::from($group)->roles();

        return new JsonApiResponse(['roles' => $roles]);
    }

    public function assign(GameIdRequest $request): JsonApiResponse
    {
        $assigned = [];
        $werewolves = [];

        $gameId = $request->validated('gameId');
        $game = $this->getGame($gameId);
        $members = $this->getMembers($gameId);
        $roles = $game['roles'];

        foreach ($roles as $role => $count) {
            for ($i = 0; $i < $count; $i++) {
                $member = $this->pickMember($members, $assigned);
                $assigned[$member] = $role;

                if (in_array(Role::from($role), Team::Werewolves->roles(), true)) {
                    $werewolves[] = $member;
                }
            }
        }

        $game['assigned_roles'] = $assigned;
        $game['werewolves'] = $werewolves;

        Redis::set("game:$gameId", $game);

        broadcast(
            new WerewolvesList(
                [
                    'gameId' => $gameId,
                    'list' => $werewolves,
                ],
                true,
                $werewolves
            )
        );

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    private function pickMember(array $members, array $assigned): string
    {
        $member = $members[rand(0, count($members) - 1)]['user_id'];

        while (array_key_exists($member, $assigned)) {
            $member = $members[rand(0, count($members) - 1)]['user_id'];
        }

        return $member;
    }
}
