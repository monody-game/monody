<?php

namespace App\Events;

use App\Events\Abstract\DiscordBotEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameShare extends DiscordBotEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $payload
    ) {
    }

    public function broadcastAs(): string
    {
        return 'game.share';
    }
}
