<?php

namespace App\Http\Controllers\Api\Game;

use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Models\Role;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameUsersController extends Controller
{
    use GameHelperTrait;
    use MemberHelperTrait;

    public function list(Request $request): JsonResponse
    {
        if (!$request->has('gameId')) {
            return new JsonResponse(['error' => 'Game id is required'], Response::HTTP_BAD_REQUEST);
        }

        $id = $request->get('gameId');

        if (!Redis::exists("game:$id")) {
            return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        $game = $this->getGame($id);

        return new JsonResponse(['users' => $game['users']]);
    }

    public function role(UserRoleRequest $request): JsonResponse
    {
        /** @var string $gameId */
        $gameId = $request->user()?->current_game;
        /** @var string[] $game */
        $game = $this->getGame($gameId);
        $userRole = $game['assigned_roles'][$request->validated('id')];
        $role = Role::find($userRole);

        return new JsonResponse($role);
    }
}
