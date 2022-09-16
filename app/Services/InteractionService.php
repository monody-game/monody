<?php

namespace App\Services;

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
    const INTERACTION_DOES_NOT_EXISTS = 1;

    const NOT_ANY_INTERACTION_STARTED = 2;

    const USER_CANNOT_USE_THIS_INTERACTION = 3;

    const INVALID_ACTION_ON_INTERACTION = 4;

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
            'interactionId' => (string) Str::uuid(),
            'authorizedCallers' => $callers,
            'type' => $type->value,
        ];

        Redis::set($key, [$interaction, ...(Redis::get($key) ?? [])]);

        return $interaction;
    }

    public function close(string $gameId, string $interactionId): int|null
    {
        if (!Redis::exists("game:$gameId:interactions")) {
            return self::NOT_ANY_INTERACTION_STARTED;
        }

        $interactions = Redis::get("game:$gameId:interactions");
        $interaction = array_search($interactionId, array_column($interactions, 'interactionId'), true);

        if ($interaction === false) {
            return self::INTERACTION_DOES_NOT_EXISTS;
        }

        array_splice($interactions, $interaction, 1);

        Redis::set("game:$gameId:interactions", $interactions);

        return null;
    }

    public function getInteraction(string $gameId, string $interactionId): array
    {
        $interactions = Redis::get("game:$gameId:interactions") ?? [];
        $interaction = array_search($interactionId, array_column($interactions, 'interactionId'), true);

        if ($interaction === false) {
            return [];
        }

        return $interactions[$interaction];
    }

    public function call(InteractionActions $action, string $interactionId, string $emitterId, string $targetId): mixed
    {
        $interaction = $this->getInteraction($this->getCurrentUserGameActivity($emitterId), $interactionId);
        $type = explode(':', $action->value);

        if ($type[0] !== $interaction['type']) {
            return self::INVALID_ACTION_ON_INTERACTION;
        }

        switch ($type[0]) {
            case 'psychic':
                $service = new PsychicAction;
                break;
            case 'witch':
                $service = new WitchAction;
                break;
            case 'werewolves':
                $service = new WerewolvesAction;
                break;
            case 'vote':
                $service = new VoteAction;
                break;
        }

        /** @phpstan-ignore-next-line */
        if (!$service->canInteract($action, $emitterId, $targetId)) {
            return self::USER_CANNOT_USE_THIS_INTERACTION;
        }

        /** @phpstan-ignore-next-line */
        return $service->call($targetId, $action);
    }
}
