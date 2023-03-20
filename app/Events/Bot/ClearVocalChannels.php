<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class ClearVocalChannels extends DiscordBotEvent
{
	/**
	 * @param array{game_id: string} $payload
	 */
	public function __construct(
		public array $payload
	)
	{
	}

	public function broadcastAs(): string
    {
        return 'game.vocal.clear';
    }
}
