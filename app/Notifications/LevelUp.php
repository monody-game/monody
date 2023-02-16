<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LevelUp extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $payload
    ) {
    }

    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_id' => $this->payload['user_id'],
            'level' => $this->payload['level'],
            'exp_needed' => $this->payload['exp_needed'],
        ]);
    }

    public function broadcastType(): string
    {
        return 'exp.levelup';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['broadcast'];
    }
}
