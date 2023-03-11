<?php

namespace App\Notifications;

use App\Enums\Badges;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BadgeGranted extends Notification
{
    use Queueable;

    /**
     * @param  array{badge: Badges, level: int}  $payload
     */
    public function __construct(
        public array $payload
    ) {
    }

    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'badge' => $this->payload['badge']->full(),
            'level' => $this->payload['level'],
        ]);
    }

    public function via(): array
    {
        return ['broadcast'];
    }

    public function broadcastType(): string
    {
        return 'badge.granted';
    }
}
