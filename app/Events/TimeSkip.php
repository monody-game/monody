<?php

namespace App\Events;

use App\Events\Abstract\WebsocketsServerEvent;

class TimeSkip extends WebsocketsServerEvent
{
    protected string $event = 'time.skip';

    public function __construct(
        public string $gameId,
        public int $to
    ) {
    }
}
