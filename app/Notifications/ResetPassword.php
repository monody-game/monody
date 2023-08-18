<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly string $token)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(__('mail.reset.title'))
            ->line(__('mail.reset.content'))
            ->action(__('mail.reset.action'), config('app.url') . '/forgot/' . $this->token)
            ->line(__('mail.reset.expire_time', ['time' => config('auth.passwords.users.expire')]))
            ->line(__('mail.reset.note'));
    }
}
