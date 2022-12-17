<?php

namespace App\Services;

use App\Actions\ActionInterface;
use App\Actions\PsychicAction;
use App\Actions\VoteAction;
use App\Actions\WerewolvesAction;
use App\Actions\WitchAction;
use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Facades\Redis;
use App\Traits\RegisterHelperTrait;
use Illuminate\Support\Str;

class InteractionService
{
    const INTERACTION_DOES_NOT_EXISTS = 1000;

    const NOT_ANY_INTERACTION_STARTED = 2000;

    const USER_CANNOT_USE_THIS_INTERACTION = 3000;

    const INVALID_ACTION_ON_INTERACTION = 4000;

    use RegisterHelperTrait;

    /**
     * Create and start an interaction with the given parameters
     *
     * @param  string  $gameId The id of the game
     * @param  Interactions  $type The type of the interaction (vote, ...)
     * @param  array|string  $authorizedCallers The authorized users to use the interaction (by default it is everyone)
     * @return string[]
     */
    public function create(string $gameId, Interactions $type, array|string $authorizedCallers = '*'): array
    {
        $key = "game:$gameId:interactions";
        /** @var string $callers */
        $callers = is_string($authorizedCallers) ? $authorizedCallers : json_encode($authorizedCallers);

        $interaction = [
            'gameId' => $gameId,
            'id' => (string) Str::uuid(),
            'authorizedCallers' => $callers,
            'type' => $type->value,
        ];

        Redis::set($key, [$interaction, ...(Redis::get($key) ?? [])]);

        return $interaction;
    }

    /**
     * @param  string  $id Interaction id
     */
    public function close(string $gameId, string $id): int|null
    {
        if (!Redis::exists("game:$gameId:interactions")) {
            return self::NOT_ANY_INTERACTION_STARTED;
        }

        $interactions = Redis::get("game:$gameId:interactions");
        $interaction = array_search($id, array_column($interactions, 'id'), true);

        if ($interaction === false) {
            return self::INTERACTION_DOES_NOT_EXISTS;
        }

        $service = $this->getService($interactions[$interaction]['type']);

        $service->close($gameId);

        array_splice($interactions, $interaction, 1);

        Redis::set("game:$gameId:interactions", $interactions);

        return null;
    }

    /**
     * @param  string  $id Interaction id
     */
    public function getInteraction(string $gameId, string $id): array
    {
        $interactions = Redis::get("game:$gameId:interactions") ?? [];
        $interaction = array_search($id, array_column($interactions, 'id'), true);

        if ($interaction === false) {
            return [];
        }

        return $interactions[$interaction];
    }

    /**
     * @param  string  $id Interaction id
     */
    public function call(InteractionActions $action, string $id, string $emitterId, string $targetId): mixed
    {
        $interaction = $this->getInteraction($this->getCurrentUserGameActivity($emitterId), $id);
        $type = explode(':', $action->value)[0];

        if ($type !== $interaction['type']) {
            return self::INVALID_ACTION_ON_INTERACTION;
        }

        $service = $this->getService($type);

        if (!$service->canInteract($action, $emitterId, $targetId)) {
            return self::USER_CANNOT_USE_THIS_INTERACTION;
        }

        $status = $service->call($targetId, $action);

        $service->updateClients($emitterId);

        return $status;
    }

    private function getService(string $type): ActionInterface
    {
        return match (Interactions::from($type)) {
            Interactions::Vote => new VoteAction,
            Interactions::Witch => new WitchAction,
            Interactions::Psychic => new PsychicAction,
            Interactions::Werewolves => new WerewolvesAction,
        };
    }
}
