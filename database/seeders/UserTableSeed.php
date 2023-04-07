<?php

namespace Database\Seeders;

use App\Models\Statistic;
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
        $user = User::factory()->create([
            'username' => 'moon250',
            'email' => 'mooneupho@gmail.com',
            'level' => 5,
            'password' => Hash::make('moon250bg'),
            'avatar' => '/assets/avatars/moon250.png',
            'created_at' => Carbon::now(),
        ]);

        $stat = new Statistic();
        $stat->user_id = $user->id;
        $stat->save();

        $user = User::factory()->create([
            'username' => 'JohnDoe',
            'email' => 'johndoe@monody.fr',
            'password' => Hash::make('johndoe'),
            'created_at' => Carbon::now(),
            'level' => 3,
        ]);

        $stat = new Statistic();
        $stat->user_id = $user->id;
        $stat->save();

        $user = User::factory()->create([
            'username' => 'gerard123',
            'email' => 'gerard123@monody.fr',
            'level' => 2,
            'password' => Hash::make('gerard123'),
            'created_at' => Carbon::now(),
        ]);

        $stat = new Statistic();
        $stat->user_id = $user->id;
        $stat->save();
    }
}
