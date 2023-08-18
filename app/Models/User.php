<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasUuids;

    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'username',
        'avatar',
        'email',
        'password',
        'current_game',
        'discord_id',
        'discord_linked_at',
        'discord_token',
        'discord_refresh_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'discord_id',
        'discord_token',
        'discord_refresh_token',
        'discord_linked_at',
        'current_game',
    ];

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function clearDiscord(): void
    {
        $this->discord_id = null;
        $this->discord_token = null;
        $this->discord_refresh_token = null;
        $this->discord_linked_at = null;
        $this->save();
    }
}
