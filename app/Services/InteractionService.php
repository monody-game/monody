<?php

namespace App\Services;

use App\Enums\GameInteractions;
use App\Events\InteractionClose;
use App\Events\InteractionCreate;
use App\Facades\Redis;
use Illuminate\Support\Str;

class InteractionService
{
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

    public function close(string $gameId, string $interactionId): void
    {
        if (!Redis::exists("game:$gameId:interactions")) {
            return;
        }

        $interactions = Redis::get("game:$gameId:interactions");
        $interaction = array_search($interactionId, array_column($interactions, 'interactionId'), true);

        if ($interaction === false) {
            return;
        }

        $interaction = array_splice($interactions, $interaction, 1)[0];
        Redis::set("game:$gameId:interactions", $interactions);

        InteractionClose::broadcast([
            'gameId' => $interaction['gameId'],
            'interactionId' => $interaction['interactionId'],
        ]);
    }
}
