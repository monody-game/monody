<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('exp')->insert([
            'user_id' => 1,
            'exp' => 15,
        ]);

        DB::table('exp')->insert([
            'user_id' => 2,
            'exp' => 35,
        ]);
    }
}
