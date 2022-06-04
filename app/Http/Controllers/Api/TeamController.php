<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function all(): JsonResponse
    {
        $teams = Team::all();

        return new JsonResponse(['teams' => $teams]);
    }
}
