<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
			'password' => bcrypt('moon250bg'),
			'created_at' => Carbon::now(),
		]);

		User::factory()->create([
			'username' => 'JohnDoe',
			'avatar' => "/storage/avatars/default.png",
			'email' => 'johndoe@gmail.com',
			'password' => bcrypt('johndoe'),
			'created_at' => Carbon::now(),
		]);
    }
}
