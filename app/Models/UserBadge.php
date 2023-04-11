<?php

namespace App\Models;

use App\Enums\Badge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserBadge extends Model
{
    protected $table = 'badges';

    protected $fillable = ['user_id', 'badge_id', 'level', 'obtained_at'];

    protected $casts = [
        'badge_id' => Badge::class,
    ];

    public $timestamps = false;

    /**
     * @return Collection<int, UserBadge>
     */
    public static function getUserBadge(User $user, Badge $badge): Collection
    {
        return self::where('user_id', $user->id)->where('badge_id', $badge->value)->get();
    }
}
