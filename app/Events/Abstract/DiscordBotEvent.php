<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;

abstract class DiscordBotEvent
{
    public static string $channel = 'bot.private';

    public function broadcastOn(): Channel
    {
        return new Channel(self::$channel);
    }
}
