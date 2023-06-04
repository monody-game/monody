<?php

namespace Database\Factories;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameOutcomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'winning_role' => Role::cases()[array_rand(Role::cases())]->value,
            'round' => rand(2, 10),
            'assigned_roles' => json_encode([]),
            'owner_id' => $this->faker->uuid,
            'game_users' => json_encode([]),
            'played_at' => now()->toTimeString(),
            'winning_users' => json_encode([]),
        ];
    }
}
