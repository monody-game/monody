<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class ClearGameInvitations extends DiscordBotEvent
{
    public function broadcastAs(): string
    {
        return 'game.invitations.clear';
    }
}
