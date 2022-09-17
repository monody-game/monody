<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
use App\Enums\Rounds;
use App\Enums\States;
use App\Facades\Redis;
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

        $round = $round->stateify();

        if ($gameId !== null) {
            $game = Redis::get("game:$gameId");
            $roles = array_keys($game['roles']);

            $roles = array_map(function ($role) {
                return Roles::from($role)->name();
            }, $roles);

            foreach ($round as $key => $state) {
                if (
                    $state === States::Waiting ||
                    $state === States::Starting ||
                    $state === States::Night ||
                    $state === States::Day ||
                    $state === States::Vote
                ) {
                    continue;
                }

                if (!in_array($state->stringify(), $roles, true)) {
                    array_splice($round, $key, 1);
                }
            }
        }

        return new JsonResponse($round);
    }
}
