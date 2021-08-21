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
            'image' => '/images/roles/werewolf.png'
        ]);

        DB::table('roles')->insert([
            'name' => 'simple_villager',
            'display_name' => 'Simple villageois',
            'image' => '/images/roles/simple-villager.png'
        ]);

        DB::table('roles')->insert([
            'name' => 'psychic',
            'display_name' => 'Voyante',
            'limit' => 1
        ]);
    }
}
