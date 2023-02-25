<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatLock implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $gameId,
        public bool $private = false,
        public array $recipients = []
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->gameId}");
    }

    public function broadcastAs(): string
    {
        return 'chat.lock';
    }
}
