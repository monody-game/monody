<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Facades\Redis;
use Illuminate\Http\JsonResponse;

class GameActionsController extends GameController
{
    public function all(): JsonResponse
    {
        return new JsonResponse(Interactions::getActions());
    }

    public function get(string $gameId, string $interactionId): JsonResponse
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $interactions = Redis::get("game:$gameId:interactions") ?? [];
        $interaction = array_filter($interactions, fn ($interaction) => $interaction['id'] === $interactionId)[0];

        $actions = InteractionActions::cases();
        $actions = array_filter($actions, fn ($action) => str_starts_with($action->value, $interaction['type']) && !in_array($action, $usedActions, true));

        return new JsonResponse(['actions' => array_values($actions)]);
    }
}
