<?php

namespace Database\Factories;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameOutcomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->uuid,
            'role' => rand(1, 9),
            'win' => (bool) rand(0, 1),
            'winning_role' => Role::cases()[array_rand(Role::cases())]->value,
            'round' => rand(2, 10),
            'composition' => json_encode([]),
            'owner_id' => $this->faker->uuid,
            'users' => json_encode([]),
            'played_at' => now()->toTimeString(),
        ];
    }
}
