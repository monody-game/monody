<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamesTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('games')->insert([
            'owner_id' => 1,
            'is_started' => false
        ]);

        DB::table('game_users')->insert([
            'user_id' => 1,
            'game_id' => 1
        ]);
        
        DB::table('game_users')->insert([
            'user_id' => 2,
            'game_id' => 1
        ]);

        for($i = 1; $i < 4; $i++) {
            DB::table('game_roles')->insert([
                'game_id' => 1,
                'role_id' => $i
            ]);
        }
    }

}
