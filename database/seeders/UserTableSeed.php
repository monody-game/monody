<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
            'avatar' => '/storage/avatars/1.png',
            'level' => 100,
            'password' => bcrypt('***REMOVED***'),
			'created_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'username' => 'JohnDoe',
            'email' => 'johndoe@gmail.com',
            'password' => bcrypt('johndoe'),
			'created_at' => Carbon::now(),
        ]);
    }
}
