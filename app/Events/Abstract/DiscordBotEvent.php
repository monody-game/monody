<?php

namespace App\Events\Abstract;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class DiscordBotEvent extends AbstractEvent implements ShouldBroadcastNow
{
    public static string $channel = 'bot.private';
}
