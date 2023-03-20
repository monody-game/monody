<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class ClearVocalChannels extends DiscordBotEvent
{
    protected string $event = 'game.vocal.clear';

    /**
     * @param  array{game_id: string}  $payload
     */
    public function __construct(
        public array $payload
    ) {
    }
}
