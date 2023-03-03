<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Http\Controllers\Controller;
use App\Traits\InteractsWithRedis;
use Illuminate\Http\JsonResponse;

class GameActionsController extends Controller
{
    use InteractsWithRedis;

    public function all(): JsonResponse
    {
        return new JsonResponse(Interactions::getActions());
    }

    public function get(string $gameId, string $interactionId): JsonResponse
    {
        $usedActions = $this->redis()->get("game:$gameId:interactions:usedActions") ?? [];
        $interactions = $this->redis()->get("game:$gameId:interactions") ?? [];

        $interaction = array_filter($interactions, fn ($interaction) => $interaction['id'] === $interactionId)[0];

        $actions = InteractionActions::cases();
        $actions = array_filter($actions, fn ($action) => str_starts_with($action->value, $interaction['type']) && !in_array($action->value, $usedActions, true));

        return new JsonResponse(['actions' => array_values($actions)]);
    }
}
