<?php

namespace App\Services;

use App\Actions\PsychicAction;
use App\Actions\VoteAction;
use App\Actions\WitchAction;
use App\Enums\GameInteractions;
use App\Enums\InteractionActions;
use App\Events\InteractionClose;
use App\Events\InteractionCreate;
use App\Facades\Redis;
use App\Traits\RegisterHelperTrait;
use Illuminate\Support\Str;

class InteractionService
{
    const INTERACTION_DOES_NOT_EXISTS = 1;

    const NOT_ANY_INTERACTION_STARTED = 2;

    const USER_CANNOT_USE_THIS_INTERACTION = 3;

    use RegisterHelperTrait;

    /**
     * Creates and start an interaction with the given parameters
     *
     * @param  string  $gameId The id of the game
     * @param  GameInteractions  $type The type of the interaction (vote, ...)
     * @param  array|string  $authorizedCallers The authorized users to use the interaction (by default it is everyone)
     * @return string[]
     */
    public function create(string $gameId, GameInteractions $type, array|string $authorizedCallers = '*'): array
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

        InteractionCreate::broadcast($interaction);

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

        $interaction = array_splice($interactions, $interaction, 1)[0];
        Redis::set("game:$gameId:interactions", $interactions);

        InteractionClose::broadcast([
            'gameId' => $interaction['gameId'],
            'interactionId' => $interaction['interactionId'],
        ]);

        return null;
    }

    public function exists(string $gameId, string $interactionId): bool
    {
        $interactions = Redis::get("game:$gameId:interactions");

        return in_array($interactionId, array_column($interactions, 'interactionId'), true);
    }

    public function call(InteractionActions $action, string $emitterId, string $targetId): mixed
    {
        $role = explode(':', $action->value);

        switch ($role[0]) {
            case 'psychic':
                $service = new PsychicAction;
                break;
            case 'witch':
                $service = new WitchAction;
                break;
            case 'werewolves':
                // TODO implement werewolves action
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
