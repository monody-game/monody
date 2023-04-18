<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CouplePaired implements ShouldBroadcastNow
{
    public function __construct(
        public array $payload,
        public bool $private = true,
        public array $recipients = []
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel("game.{$this->payload['gameId']}");
    }

    public function broadcastAs(): string
    {
        return 'game.couple';
    }
}
