<?php

namespace Database\Seeders;

use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Seeder;

class SpecificScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = $this->getData();

        foreach ($items as $item) {
            DB::transaction(function () use ($item) {
                // Create the schedule
                $scheduleCleaning = ScheduleCleaning::create($item->toArray());
                $products = $scheduleCleaning->subscription->products;

                // Create the schedule employees
                foreach ($scheduleCleaning->subscription->staff as $staff) {
                    $scheduleCleaning->scheduleEmployees()->create([
                        'user_id' => $staff->user_id,
                    ]);
                }

                // Create the schedule products
                if ($products) {
                    foreach ($products as $product) {
                        $producData = Product::find($product->product_id);
                        $scheduleCleaning->products()->create([
                            'schedule_cleaning_id' => $scheduleCleaning->id,
                            'product_id' => $product->product_id,
                            'name' => $producData->name,
                            'price' => $producData->price,
                            'quantity' => $product->quantity,
                            'discount_percentage' => 0,
                        ]);
                    }
                }
            });
        }
    }

    /**
     * @return SubscriptionScheduleDTO[]
     */
    private function getData()
    {
        return [
            SubscriptionScheduleDTO::from([
                'subscription_id' => 8,
                'team_id' => 1,
                'customer_id' => 5,
                'property_id' => 5,
                'status' => ScheduleCleaningStatusEnum::Booked(),
                'start_at' => Carbon::create('2023-07-11'.'08:00:00')->toDateTimeString(),
                'end_at' => Carbon::create('2023-07-11'.'10:00:00')->toDateTimeString(),
            ]),
            SubscriptionScheduleDTO::from([
                'subscription_id' => 9,
                'team_id' => 1,
                'customer_id' => 5,
                'property_id' => 5,
                'status' => ScheduleCleaningStatusEnum::Booked(),
                'start_at' => Carbon::create('2023-07-11'.'10:00:00')->toDateTimeString(),
                'end_at' => Carbon::create('2023-07-11'.'12:00:00')->toDateTimeString(),
            ]),
            SubscriptionScheduleDTO::from([
                'subscription_id' => 11,
                'team_id' => 2,
                'customer_id' => 6,
                'property_id' => 6,
                'status' => ScheduleCleaningStatusEnum::Booked(),
                'start_at' => Carbon::create('2023-07-11'.'08:00:00')->toDateTimeString(),
                'end_at' => Carbon::create('2023-07-11'.'10:00:00')->toDateTimeString(),
            ]),
            SubscriptionScheduleDTO::from([
                'subscription_id' => 12,
                'team_id' => 2,
                'customer_id' => 6,
                'property_id' => 6,
                'status' => ScheduleCleaningStatusEnum::Booked(),
                'start_at' => Carbon::create('2023-07-11'.'10:00:00')->toDateTimeString(),
                'end_at' => Carbon::create('2023-07-11'.'12:00:00')->toDateTimeString(),
            ]),
            SubscriptionScheduleDTO::from([
                'subscription_id' => 14,
                'team_id' => 3,
                'customer_id' => 7,
                'property_id' => 7,
                'status' => ScheduleCleaningStatusEnum::Booked(),
                'start_at' => Carbon::create('2023-07-11'.'08:00:00')->toDateTimeString(),
                'end_at' => Carbon::create('2023-07-11'.'10:00:00')->toDateTimeString(),
            ]),
            SubscriptionScheduleDTO::from([
                'subscription_id' => 15,
                'team_id' => 3,
                'customer_id' => 7,
                'property_id' => 7,
                'status' => ScheduleCleaningStatusEnum::Booked(),
                'start_at' => Carbon::create('2023-07-11'.'10:00:00')->toDateTimeString(),
                'end_at' => Carbon::create('2023-07-11'.'12:00:00')->toDateTimeString(),
            ]),
        ];
    }
}
