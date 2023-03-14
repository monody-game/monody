<?php

namespace Database\Seeders;

use App\Enums\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Database\Seeder;

class BadgeTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moon = User::where('username', 'moon250')->get()->first();

        $badge = new UserBadge();
        $badge->user_id = $moon->id;
        $badge->badge_id = Badge::Beta;
        $badge->save();

        $badge = new UserBadge();
        $badge->user_id = $moon->id;
        $badge->badge_id = Badge::Owner;
        $badge->save();

        $badge = new UserBadge();
        $badge->user_id = $moon->id;
        $badge->badge_id = Badge::Wins;
        $badge->level = 3;
        $badge->save();
    }
}
