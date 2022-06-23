<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
	{
		$discord_id = [
			null,
			random_int(100000, 10000000)
		];

		$id =  $this->faker->uuid;

        return [
			'id' => $id,
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'avatar' => "/storage/avatars/{$id}.png",
            'password' => '$2y$10$DyQBGnv16XcrRLL.KraCqeW1gthGs1Mqnv.enZorm9PF8s0KO//ka',
            'level' => 23,
			'discord_id' => $discord_id[random_int(0, 1)]
        ];
    }
}
