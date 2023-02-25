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
            ->subject('Monody | Réinitialiser votre mot de passe')
            ->line('Cliquez sur le bouton ci-dessous afin de changer votre mot de passe.')
            ->action('Réinitialiser le mot de passe', config('app.url') . '/forgot/' . $this->token)
            ->line('Ce lien va expirer dans ' . config('auth.passwords.users.expire') . ' minutes.')
            ->line('Vous n\'êtes pas à l\'origine de cette demande ? Ignorez simplement ce mail.');
    }
}
