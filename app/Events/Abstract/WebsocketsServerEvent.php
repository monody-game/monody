<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class WebsocketsServerEvent extends AbstractEvent implements ShouldBroadcastNow
{
    public static string $channel = 'ws.private';

    public function broadcastOn(): Channel
    {
        return new Channel(self::$channel);
    }

    public function broadcastAs(): string
    {
        return $this->event;
    }
}
