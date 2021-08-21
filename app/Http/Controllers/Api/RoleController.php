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
}
