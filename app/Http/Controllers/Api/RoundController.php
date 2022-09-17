<?php

namespace App\Http\Controllers\Api;

use App\Enums\Rounds;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RoundController extends Controller
{
    public function all(): JsonResponse
    {
        $rounds = Rounds::cases();

        $rounds = array_map(function ($round) {
            return $round->stateify();
        }, $rounds);

        return new JsonResponse($rounds);
    }

    public function get(int $round): JsonResponse
    {
        $round = Rounds::tryFrom($round);

        if ($round === null) {
            $round = Rounds::LoopRound;
        }

        return new JsonResponse($round->stateify());
    }
}
