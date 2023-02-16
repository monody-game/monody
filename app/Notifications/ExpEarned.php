<?php

namespace App\Notifications;

use App\Models\Exp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ExpEarned extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Exp $exp)
    {
    }

    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_id' => $this->exp->user_id,
            'amount' => $this->exp->exp,
        ]);
    }

    public function broadcastType(): string
    {
        return 'exp.earn';
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
