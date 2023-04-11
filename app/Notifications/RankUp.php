<?php

namespace App\Notifications;

use App\Enums\Rank;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RankUp extends Notification
{
    use Queueable;

    public function __construct(
        public string $userId,
        public Rank $rank
    ) {
    }

    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_id' => $this->userId,
            'rank' => $this->rank,
        ]);
    }

    public function broadcastType(): string
    {
        return 'elo.rankup';
    }

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }
}
