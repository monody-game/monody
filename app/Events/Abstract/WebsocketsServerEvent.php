<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;

abstract class WebsocketsServerEvent
{
    public function broadcastOn(): Channel
    {
        return new Channel('ws.private');
    }
}
