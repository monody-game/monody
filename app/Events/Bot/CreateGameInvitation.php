<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class CreateGameInvitation extends DiscordBotEvent
{
    public function __construct(
        public array $payload
    ) {
    }

    public function broadcastAs(): string
    {
        return 'game.invite';
    }
}
