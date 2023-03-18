<?php

namespace App\Events;

use App\Events\Abstract\WebsocketsServerEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimeSkip extends WebsocketsServerEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $gameId,
        public int $to
    ) {
    }

    public function broadcastAs(): string
    {
        return 'time.skip';
    }
}
