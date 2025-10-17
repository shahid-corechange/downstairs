<?php

namespace Database\Factories;

use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit' => ProductUnitEnum::Piece(),
            'price' => fake()->randomFloat(2, 1, 100),
            'credit_price' => fake()->randomFloat(2, 1, 100),
            'vat_group' => VatNumbersEnum::TwentyFive(),
            'has_rut' => false,
            'thumbnail_image' => null,
            'color' => fake()->colorName(),
        ];
    }
}
