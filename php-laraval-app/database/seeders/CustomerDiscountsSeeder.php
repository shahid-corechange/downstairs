<?php

namespace Database\Seeders;

use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Models\CustomerDiscount;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerDiscountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            CustomerDiscount::create([
                'user_id' => $user->id,
                'type' => CustomerDiscountTypeEnum::Cleaning(),
                'value' => fake()->numberBetween(1, 10) * 10,
                'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
                'end_date' => fake()->dateTimeBetween('now', '+1 month'),
                'usage_limit' => fake()->numberBetween(1, 10),
            ]);

            CustomerDiscount::create([
                'user_id' => $user->id,
                'type' => CustomerDiscountTypeEnum::Laundry(),
                'value' => fake()->numberBetween(1, 10) * 10,
                'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
                'end_date' => fake()->dateTimeBetween('now', '+1 month'),
                'usage_limit' => fake()->numberBetween(1, 10),
            ]);
        }
    }
}
