<?php

namespace App\Events\Abstract;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class WebsocketsServerEvent extends AbstractEvent implements ShouldBroadcastNow
{
    public static string $channel = 'ws.private';
}
