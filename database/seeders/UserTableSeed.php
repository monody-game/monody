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
            'email' => '***REMOVED***',
            'password' => bcrypt('***REMOVED***')
        ]);

        DB::table('users')->insert([
            'username' => 'JohnDoe',
            'email' => 'johndoe@gmail.com',
            'password' => bcrypt('john')
        ]);
    }
}
