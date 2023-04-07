<?php

namespace Database\Factories;

use App\Models\Elo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloFactory extends Factory
{
    protected $model = Elo::class;

    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()->id,
            'elo' => 2000,
        ];
    }
}
