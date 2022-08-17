<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpTableSeed extends Seeder
{
    public function run()
    {
        $users = User::all();

        DB::table('exp')->insert([
            'user_id' => $users->first()->id,
            'exp' => 15,
        ]);

        DB::table('exp')->insert([
            'user_id' => $users[1]->id,
            'exp' => 35,
        ]);
    }
}
