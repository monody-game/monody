<?php

namespace App\Http\Controllers\Api;

use App\Enums\Teams;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function all(): JsonResponse
    {
        $teams = Teams::all();

        return new JsonResponse(['teams' => $teams]);
    }

    public function get(int $id): JsonResponse
    {
        $team = Teams::from($id);

        return new JsonResponse(['team' => [
            'id' => $id,
            'name' => $team->name(),
            'display_name' => $team->stringify(),
        ]]);
    }
}
