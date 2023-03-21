<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class ClearVoiceChannels extends DiscordBotEvent
{
    protected string $event = 'game.voice.clear';

    /**
     * @param  array{channel_id: string, game_id: string}  $payload
     */
    public function __construct(
        public array $payload
    ) {
    }
}
