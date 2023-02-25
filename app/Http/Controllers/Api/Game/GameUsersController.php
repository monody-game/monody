<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;

class GameUsersController extends Controller
{
    use GameHelperTrait;
    use MemberHelperTrait;

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
        $role = Roles::from($userRole)->full();

        return new JsonResponse($role);
    }
}
