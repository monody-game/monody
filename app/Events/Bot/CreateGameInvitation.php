<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class CreateGameInvitation extends DiscordBotEvent
{
    protected string $event = 'game.invite';

    public function __construct(
        public array $payload
    ) {
    }
}
