<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class GameShare extends DiscordBotEvent
{
    public function __construct(
        public array $payload
    ) {
    }

    public function broadcastAs(): string
    {
        return 'game.share';
    }
}
