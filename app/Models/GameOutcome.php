<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GameOutcome extends Model
{
    use HasFactory;

    protected $table = 'game_outcomes';

    public $timestamps = false;

    protected $casts = [
        'winning_role' => Role::class,
        'assigned_roles' => 'array',
        'game_users' => 'array',
        'played_at' => 'datetime',
        'winning_users' => 'array',
    ];

    protected $fillable = [
        'winning_role',
        'assigned_roles',
        'winning_users',
        'owner_id',
        'game_users',
        'round',
        'played_at',
    ];

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('win', 'role', 'death_round', 'death_context');
    }
}
