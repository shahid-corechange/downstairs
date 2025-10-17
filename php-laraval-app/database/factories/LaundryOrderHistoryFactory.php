<?php

namespace Database\Factories;

use App\Enums\LaundryOrder\LaundryOrderHistoryTypeEnum;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaundryOrderHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'laundry_order_id' => LaundryOrder::inRandomOrder()->first()->id,
            'type' => fake()->randomElement(LaundryOrderHistoryTypeEnum::values()),
            'note' => fake()->sentence(),
            'causer_id' => User::role('Superadmin')->inRandomOrder()->first()->id,
        ];
    }

    public function forOrder(int $laundryOrderId): self
    {
        return $this->state([
            'laundry_order_id' => $laundryOrderId,
        ]);
    }
}
