<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function all(): JsonResponse
    {
        $roles = Role::all();

        return response()->json(['roles' => $roles]);
    }

    public function get(int $id): JsonResponse
    {
        $role = Role::find($id);

        if ($role) {
            return response()->json(['role' => $role]);
        }

        return response()->json(['error' => 'Role not found'], 404);
    }
}
