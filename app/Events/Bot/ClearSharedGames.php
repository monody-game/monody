<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class ClearSharedGames extends DiscordBotEvent
{
    public function broadcastAs(): string
    {
        return 'game.share.clear';
    }
}
