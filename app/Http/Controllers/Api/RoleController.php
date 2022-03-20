<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $roles = Role::all();

        return response()->json(['roles' => $roles]);
    }

    public function get(Request $request, int $id): JsonResponse
    {
        $role = Role::find($id);

        if($role) {
            return response()->json(['role' => $role]);
        } else {
            return response()->json(['error' => 'Role not found'], 404);
        }
    }
}
