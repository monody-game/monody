<?php

namespace App\Events\Websockets;

use App\Events\Abstract\WebsocketsServerEvent;

class GameStart extends WebsocketsServerEvent
{
    protected string $event = 'game.start';

    public function __construct(
        public array $game,
    ) {
    }
}
