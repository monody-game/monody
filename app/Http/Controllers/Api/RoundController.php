<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Round;
use App\Enums\State;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;

class RoundController extends Controller
{
    public function all(?string $gameId = null): JsonApiResponse
    {
        $rounds = [];

        foreach (Round::cases() as $round) {
            $rounds[] = $this->getRound($round->value, $gameId);
        }

        return new JsonApiResponse(['rounds' => $rounds]);
    }

    public function get(int $round, ?string $gameId = null): JsonApiResponse
    {
        return new JsonApiResponse([
            'round' => $this->getRound($round, $gameId),
        ]);
    }

    private function getRound(int $round, ?string $gameId = null): array
    {
        $round = Round::tryFrom($round);
        $removedStates = [];

        if ($round === null) {
            $round = Round::LoopRound;
        }

        $round = $round->stateify();

        if ($gameId !== null && Redis::exists("game:$gameId")) {
            $game = Redis::get("game:$gameId");
            $gameState = Redis::get("game:$gameId:state");
            $roles = array_keys($game['roles']);

            $roles = array_map(function ($role) {
                return Role::from($role)->name();
            }, $roles);

            foreach ($round as $key => $state) {
                if (
                    !$state->isRoleState()
                ) {
                    continue;
                }

                if ($gameState['status'] === $state->value) {
                    continue;
                }

                if (
                    !in_array($state->stringify(), $roles, true) &&
                    count(array_filter($roles, fn ($role) => str_contains($role, $state->stringify()))) === 0
                ) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);

                    continue;
                }

                if (!$state->hasActionsLeft($gameId)) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);
                }

                if ($state === State::WhiteWerewolf && ($gameState['round'] % 2 === 0 || $gameState['round'] === 0)) {
                    $removedStates[] = array_splice($round, ($key - count($removedStates)), 1);
                }
            }
        }

        return array_map(function ($state) {
            return [
                'identifier' => $state->value,
                'raw_name' => $state->stringify(),
                'duration' => $state->duration(),
            ];
        }, $round);
    }
}
