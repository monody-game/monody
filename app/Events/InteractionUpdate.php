<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InteractionUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly array $payload,
        public bool $private = false,
		public array $emitters = []
    ) {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel("game.{$this->payload['gameId']}");
    }

    public function broadcastAs(): string
    {
		return "interaction.{$this->payload['type']}";
    }
}
