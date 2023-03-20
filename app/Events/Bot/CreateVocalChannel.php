<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class CreateVocalChannel extends DiscordBotEvent
{
    /**
     * @param  array{owner: array{username: string, discord_id: ?string}, game_id: string, size: int}  $payload
     */
    public function __construct(
        public readonly array $payload
    ) {
    }

    public function broadcastAs(): string
    {
        return 'game.vocal.create';
    }
}
