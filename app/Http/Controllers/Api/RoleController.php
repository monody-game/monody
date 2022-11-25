<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRolesRequest;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function all(): JsonResponse
    {
        $roles = Roles::all();

        return new JsonResponse(['roles' => $roles]);
    }

    public function get(int $id): JsonResponse
    {
        $role = Roles::tryFrom($id);

        if ($role !== null) {
            return new JsonResponse(['role' => $role->full()]);
        }

        return new JsonResponse(['error' => 'Role not found'], Response::HTTP_NOT_FOUND);
    }

    public function group(int $group): JsonResponse
    {
        $roles = Teams::from($group)->roles();

        return new JsonResponse(['roles' => $roles]);
    }

    public function assign(AssignRolesRequest $request): JsonResponse
    {
        $assigned = [];
        $gameId = $request->validated('gameId');
        $game = $this->getGame($gameId);
        $members = $this->getMembers($gameId);
        $roles = $game['roles'];

        foreach ($roles as $role => $count) {
            if ($count > 1) {
                for ($i = 0; $i < $count; $i++) {
                    $member = $this->pickMember($members, $assigned);
                    $assigned[$member] = $role;
                }

                continue;
            }

            $assigned[$this->pickMember($members, $assigned)] = $role;
        }

        $game['assigned_roles'] = $assigned;

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
