<?php

namespace Database\Seeders;

use App\Enums\Badges;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Seeder;

class BadgeTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moon = User::where('username', 'moon250')->get()->first();

		$badge = new Badge();
        $badge->user_id = $moon->id;
        $badge->badge_id = Badges::Beta;
        $badge->save();

		$badge = new Badge();
		$badge->user_id = $moon->id;
		$badge->badge_id = Badges::Owner;
		$badge->save();
    }
}
