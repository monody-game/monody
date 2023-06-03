<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameOutcome extends Model
{
    use HasFactory;

    protected $table = 'game_outcome';

    public $timestamps = false;

    protected $casts = [
        'role' => Role::class,
        'win' => 'boolean',
        'winning_role' => Role::class,
        'composition' => 'array',
        'users' => 'array',
        'played_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'role',
        'win',
        'winning_role',
        'round',
        'composition',
        'owner_id',
        'users',
        'played_at',
    ];
}
