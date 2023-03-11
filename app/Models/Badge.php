<?php

namespace App\Models;

use App\Enums\Badges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Badge extends Model
{
    protected $table = 'badges';

    protected $fillable = ['user_id', 'badge_id', 'level', 'obtained_at'];

    public $timestamps = false;

    /**
     * @return Collection<int, Badge>
     */
    public static function getUserBadge(User $user, Badges $badge): Collection
    {
        return self::where('user_id', $user->id)->where('badge_id', $badge->value)->get();
    }
}
