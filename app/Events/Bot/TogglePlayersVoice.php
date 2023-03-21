<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class TogglePlayersVoice extends DiscordBotEvent
{
    protected string $event = 'game.voice.toggle';

    /**
     * @param  array{game_id: string}  $payload
     */
    public function __construct(
        public array $payload
    ) {
    }
}
