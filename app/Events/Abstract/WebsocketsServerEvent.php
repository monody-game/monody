<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class WebsocketsServerEvent implements ShouldBroadcastNow
{
    public static string $channel = 'ws.private';

    protected string $event;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function broadcastOn(): Channel
    {
        return new Channel(self::$channel);
    }

    public function broadcastAs(): string
    {
        return $this->event;
    }
}
