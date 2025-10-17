<?php

namespace Database\Seeders;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Models\FixedPrice;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class FixedPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            $startDate = [null, fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d')];
            $endDate = [null, fake()->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d')];

            $fixedPrice = FixedPrice::create([
                'user_id' => $subscription->user_id,
                'start_date' => $startDate[fake()->numberBetween(0, 1)],
                'end_date' => $endDate[fake()->numberBetween(0, 1)],
            ]);
            $subscription->update(['fixed_price_id' => $fixedPrice->id]);
            $hasRut = $subscription->customer->membership_type === MembershipTypeEnum::Private() ? true : false;

            $fixedPrice->rows()->createMany([
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => (fake()->numberBetween(1, 10) * 100) / (1 + 25 / 100),
                    'vat_group' => 25,
                    'has_rut' => $hasRut,
                ],
            ]);
        }
    }
}
