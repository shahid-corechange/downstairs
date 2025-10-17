<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'squarefeet' => fake()->numberBetween(80, 240),
            'price_per_quarters' => 122.80,
            'price_per_squarefeet' => 20,
            'price_material' => 17.60,
            'price_establish' => 87.20,
            'vat_id' => 25,
        ];
    }
}
