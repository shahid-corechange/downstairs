<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->firstName,
            'avatar' => null,
            'color' => fake()->safeHexColor(),
            'description' => fake()->sentence(),
        ];
    }
}
