<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use UUID;

    // TODO: move to trait and fix UUID trait
    public $incrementing = false;
    public $keyType = 'string';

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'avatar',
        'email',
        'password',
        'discord_id',
        'discord_token',
        'discord_refresh_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    // @phpstan-ignore-next-line
    protected $hidden = [
        'email',
        'password',
        'remember_token',
        'email_verified_at',
        'discord_token',
        'discord_refresh_token',
    ];
}
