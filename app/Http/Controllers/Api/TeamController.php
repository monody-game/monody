<?php

namespace App\Http\Controllers\Api;

use App\Enums\Team;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;

class TeamController extends Controller
{
    public function all(): JsonApiResponse
    {
        $teams = Team::all();

        return new JsonApiResponse(['teams' => $teams]);
    }

    public function get(int $id): JsonApiResponse
    {
        $team = Team::from($id);

        return new JsonApiResponse(['team' => $team->full()]);
    }
}
