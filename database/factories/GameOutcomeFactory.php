<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GameOutcomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->uuid,
            'role_id' => rand(1, 9),
            'win' => (bool) rand(0, 1),
        ];
    }
}
