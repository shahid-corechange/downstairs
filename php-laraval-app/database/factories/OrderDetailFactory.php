<?php

namespace Database\Factories;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $product = $this->faker->randomElement($this->getProducts());

        return [
            'product_id' => $product['id'],
            'quantity' => $this->faker->randomFloat(2, 0, 100),
            'discount_percentage' => $this->faker->randomNumber(2),
            'description' => $this->faker->paragraph(),
            'internal_note' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(OrderStatusEnum::values()),
        ];
    }

    /**
     * Get available products.
     *
     * @return App\Models\Product[]
     */
    private function getProducts(): array
    {
        return Product::all()->toArray();
    }
}
