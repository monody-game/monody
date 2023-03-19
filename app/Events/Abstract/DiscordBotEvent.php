<?php

namespace App\Events\Abstract;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class DiscordBotEvent implements ShouldBroadcastNow
{
    public static string $channel = 'bot.private';

    public function broadcastOn(): Channel
    {
        return new Channel(self::$channel);
    }
}
