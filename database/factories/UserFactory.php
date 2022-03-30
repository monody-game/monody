<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => 1,
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'avatar' => '/images/avatars/1.png',
            'password' => '$2y$10$DyQBGnv16XcrRLL.KraCqeW1gthGs1Mqnv.enZorm9PF8s0KO//ka',
            'level' => 23
        ];
    }
}
