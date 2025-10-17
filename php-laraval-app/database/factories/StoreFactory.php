<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'address_id' => Address::factory()->create()->id,
            'name' => 'Store '.fake()->unique()->firstName,
            'company_number' => fake()->unique()->numerify('##########'),
            'phone' => fake()->unique()->numerify('467########'),
            'dial_code' => '46',
            'email' => fake()->unique()->safeEmail,
        ];
    }
}
