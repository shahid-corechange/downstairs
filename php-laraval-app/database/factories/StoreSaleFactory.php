<?php

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
use App\Enums\StoreSale\StoreSaleStatusEnum;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        /** @var User $causer */
        $causer = User::role('Superadmin')->inRandomOrder()->first();

        return [
            'store_id' => Store::inRandomOrder()->first()->id,
            'causer_id' => $causer->id,
            'payment_method' => fake()->randomElement([
                PaymentMethodEnum::Cash(),
                PaymentMethodEnum::CreditCard(),
            ]),
            'status' => StoreSaleStatusEnum::Paid(),
        ];
    }
}
