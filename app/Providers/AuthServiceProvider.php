<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Monody | Vérifiez votre adresse mail')
                ->line('Cliquez sur le bouton ci-dessous afin de vérifier votre adresse mail.')
                ->action('Vérifier', $url);
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return config('app.url') . '/reset/' . $token;
        });

        ResetPassword::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Monody | Réinitialiser votre mot de passe')
                ->line('Cliquez sur le bouton ci-dessous afin de changer votre mot de passe.')
                ->action('Réinitialiser le mot de passe', $url)
                ->line('Ce lien va expirer dans ' . config('auth.passwords.users.expire') . ' minutes.')
                ->line('Vous n\'êtes pas à l\'origine de cette demande ? Ignorez simplement ce mail.');
        });
    }
}
