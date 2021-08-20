<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'moon250',
            'email' => 'mooneupho@gmail.com',
            'avatar' => '/images/avatars/1.png',
            'password' => bcrypt('moon250bg')
        ]);

        DB::table('users')->insert([
            'username' => 'JohnDoe',
            'email' => 'johndoe@gmail.com',
            'password' => bcrypt('johndoe')
        ]);
    }
}
