<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class UpdateVoiceChannelPermissions extends DiscordBotEvent
{
    protected string $event = 'game.voice.allow';

    /**
     * @param  array{discord_id: string, game_id: string}  $payload
     */
    public function __construct(
        public array $payload
    ) {
    }
}
