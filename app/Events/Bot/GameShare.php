<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameShare extends DiscordBotEvent
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
