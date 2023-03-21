<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CloseVoiceChannelNotice implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $gameId,
        public bool $private = true,
        public array $recipients = []
    ) {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel("game.$this->gameId");
    }

    public function broadcastAs(): string
    {
        return 'voice-notice.close';
    }
}
