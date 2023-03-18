<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;

abstract class DiscordBotEvent
{
    public function broadcastOn(): Channel
    {
        return new Channel('bot.private');
    }
}
