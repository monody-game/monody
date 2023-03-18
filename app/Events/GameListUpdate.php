<?php

namespace App\Events;

use App\Events\Abstract\DiscordBotEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameListUpdate implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public array $games;

    public bool $volatile = true;

    public function __construct(array $games)
    {
        $this->games = $games;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
	{
        return [
			new Channel('home'),
			new Channel(DiscordBotEvent::$channel)
		];
    }

    public function broadcastAs(): string
    {
        return 'game-list.update';
    }
}
