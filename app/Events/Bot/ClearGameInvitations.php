<?php

namespace App\Events\Bot;

use App\Events\Abstract\DiscordBotEvent;

class ClearGameInvitations extends DiscordBotEvent
{
    protected string $event = 'game.invitations.clear';
}
