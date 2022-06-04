<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameUnvote implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly array $payload)
    {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel("game.{$this->payload['gameId']}");
    }

    public function broadcastAs(): string
    {
        return 'game.unvote';
    }
}
