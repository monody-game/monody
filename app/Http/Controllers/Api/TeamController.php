<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $teams = Team::all();

        return response()->json(['teams' => $teams]);
    }
}
