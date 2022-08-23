<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InteractionClose implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public array $payload)
    {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('game.' . $this->payload['gameId']);
    }

    public function broadcastAs(): string
    {
        return 'interaction.close';
    }
}
