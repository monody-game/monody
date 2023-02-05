<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'username' => 'moon250',
            'email' => 'mooneupho@gmail.com',
            'level' => 100,
            'password' => Hash::make('moon250bg'),
            'created_at' => Carbon::now(),
        ]);

        User::factory()->create([
            'username' => 'JohnDoe',
            'avatar' => '/storage/avatars/default.png',
            'email' => 'johndoe@monody.fr',
            'password' => Hash::make('johndoe'),
            'created_at' => Carbon::now(),
        ]);

		User::factory()->create([
			'username' => 'gerard123',
			'avatar' => '/storage/avatars/default.png',
			'email' => 'gerard123@monody.fr',
			'password' => Hash::make('gerard123'),
			'created_at' => Carbon::now(),
		]);
    }
}
