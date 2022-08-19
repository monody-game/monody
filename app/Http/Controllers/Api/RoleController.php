<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function all(): JsonResponse
    {
        $roles = Role::all();

        return new JsonResponse(['roles' => $roles]);
    }

    public function get(int $id): JsonResponse
    {
        $role = Role::find($id);

        if ($role) {
            return new JsonResponse(['role' => $role]);
        }

        return new JsonResponse(['error' => 'Role not found'], Response::HTTP_NOT_FOUND);
    }

    public function group(int $group): JsonResponse
    {
        $roles = Role::where('team_id', '=', $group)->get('id')->toArray();

        $roles = array_map(fn ($role) => $role['id'], $roles);

        return new JsonResponse(['roles' => $roles]);
    }
}
