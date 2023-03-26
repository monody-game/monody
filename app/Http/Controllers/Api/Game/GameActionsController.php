<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;

class GameActionsController extends Controller
{
    public function all(): JsonApiResponse
    {
        return new JsonApiResponse([
            'actions' => Interaction::getActions(),
        ]);
    }

    public function get(string $gameId, string $interactionId): JsonApiResponse
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $interactions = Redis::get("game:$gameId:interactions") ?? [];

        $interaction = array_filter($interactions, fn ($interaction) => $interaction['id'] === $interactionId)[0];

        $actions = InteractionAction::cases();
        $actions = array_filter($actions, fn ($action) => str_starts_with($action->value, $interaction['type']) && !in_array($action->value, $usedActions, true));

        return new JsonApiResponse(['actions' => array_values($actions)]);
    }
}
