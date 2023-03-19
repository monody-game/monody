<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;

abstract class WebsocketsServerEvent
{
    public static string $channel = 'ws.private';

    public function broadcastOn(): Channel
    {
        return new Channel(self::$channel);
    }
}
