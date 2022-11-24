<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
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
}
