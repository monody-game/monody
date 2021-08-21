<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('teams')->insert([
            'name' => 'villagers',
            'display_name' => 'Villageois'
        ]);
        
        DB::table('teams')->insert([
            'name' => 'werewolfs',
            'display_name' => 'Loups-garous'
        ]);
        
        DB::table('teams')->insert([
            'name' => 'solos',
            'display_name' => 'Solos'
        ]);
    }
}
