<?php

namespace Database\Factories;

use App\Enums\VatNumbersEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaundryPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'price' => fake()->randomFloat(2, 10, 100),
            'percentage' => fake()->randomFloat(2, 10, 100),
            'vat_group' => VatNumbersEnum::TwentyFive(),
            'hours' => fake()->randomElement([24, 48, 72]),
        ];
    }
}
