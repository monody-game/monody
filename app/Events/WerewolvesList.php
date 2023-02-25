<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WerewolvesList implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
        public bool $private = true,
        public array $recipients = []
    ) {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel("game.{$this->payload['gameId']}");
    }

    public function broadcastAs(): string
    {
        return 'game.werewolves';
    }
}
