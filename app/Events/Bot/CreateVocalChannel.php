<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class CreateVocalChannel extends DiscordBotEvent
{
    protected string $event = 'game.vocal.create';

    /**
     * @param  array{owner: array{username: string, discord_id: ?string}, game_id: string, size: int}  $payload
     */
    public function __construct(
        public readonly array $payload
    ) {
    }
}
