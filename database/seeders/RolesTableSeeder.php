<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => 'werewolf',
            'display_name' => 'Loup-garou',
            'image' => '/images/roles/werewolf.png',
            'weight' => 1,
            'team_id' => 2
        ]);

        DB::table('roles')->insert([
            'name' => 'simple_villager',
            'display_name' => 'Simple villageois',
            'image' => '/images/roles/simple-villager.png',
            'weight' => 1,
            'team_id' => 1
        ]);

        DB::table('roles')->insert([
            'name' => 'psychic',
            'display_name' => 'Voyante',
            'image' => '/images/roles/psychic.png',
            'limit' => 1,
            'weight' => 3,
            'team_id' => 1
        ]);

        DB::table('roles')->insert([
            'name' => 'witch',
            'display_name' => 'Sorcière',
            'limit' => 1,
            'weight' => 3,
            'team_id' => 1
        ]);
    }
}
