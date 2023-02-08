<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function all(): JsonResponse
    {
        return new JsonResponse(['roles' => Roles::all()]);
    }

    public function game(string $gameId): JsonResponse
    {
        $game = $this->getGame($gameId);
        $roles = array_map(fn ($role) => Roles::from($role)->full(), array_keys($game['roles']));

        return new JsonResponse($roles);
    }

    public function get(int $id): JsonResponse
    {
        $role = Roles::tryFrom($id);

        if ($role !== null) {
            return new JsonResponse(['role' => $role->full()]);
        }

        return (new JsonResponse([], Response::HTTP_NOT_FOUND))
            ->withMessage("Role with id $id not found.");
    }

    public function group(int $group): JsonResponse
    {
        $roles = Teams::from($group)->roles();

        return new JsonResponse(['roles' => $roles]);
    }

    public function assign(GameIdRequest $request): JsonResponse
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

                if (Teams::role(Roles::from($role)) === Teams::Werewolves) {
                    $werewolves[] = $member;
                }
            }
        }

        $game['assigned_roles'] = $assigned;
        $game['werewolves'] = $werewolves;

        Redis::set("game:{$gameId}", $game);

        return new JsonResponse();
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
