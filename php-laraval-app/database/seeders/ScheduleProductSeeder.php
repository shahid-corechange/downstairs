<?php

namespace Database\Seeders;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use Illuminate\Database\Seeder;

class ScheduleProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())->get();

        foreach ($schedules as $schedule) {
            foreach ($schedule->subscription->products as $product) {
                $producData = Product::find($product->product_id);
                $schedule->products()->create([
                    'schedule_cleaning_id' => $schedule->id,
                    'product_id' => $product->product_id,
                    'price' => $producData->price,
                    'quantity' => $product->quantity,
                    'discount_percentage' => 0,
                ]);
            }
        }
    }
}
