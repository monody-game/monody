<?php

namespace App\Http\Controllers\Api;

use App\Enums\Team;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function all(): JsonResponse
    {
        $teams = Team::all();

        return new JsonResponse(['teams' => $teams]);
    }

    public function get(int $id): JsonResponse
    {
        $team = Team::from($id);

        return new JsonResponse(['team' => $team->full()]);
    }
}
