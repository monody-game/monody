<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable /*implements MustVerifyEmail*/
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
        'discord_token',
        'discord_refresh_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'discord_token',
        'discord_refresh_token',
    ];
}
