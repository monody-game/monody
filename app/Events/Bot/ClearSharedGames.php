<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClearSharedGames extends DiscordBotEvent
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public function broadcastAs(): string
	{
		return 'game.share.clear';
	}
}
