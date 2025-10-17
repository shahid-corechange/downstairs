<?php

namespace Database\Seeders;

use App\DTOs\Schedule\BuildScheduleDTO;
use App\Models\CustomerDiscount;
use App\Models\LaundryOrder;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleLaundry;
use App\Models\User;
use App\Services\LaundryOrder\LaundryOrderService;
use DB;
use Illuminate\Database\Seeder;

class LaundryOrderSeeder extends Seeder
{
    public function __construct(
        protected LaundryOrderService $laundryOrderService,
    ) {
    }

    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            LaundryOrder::factory()->count(10)->forStore(1)->create();
            LaundryOrder::factory()->count(10)->forStore(2)->create();
            LaundryOrder::factory()->count(10)->forStore(3)->create();
        } else {
            LaundryOrder::factory()->count(5)->forStore(1)->create();
        }

        $laundryOrders = LaundryOrder::all();
        foreach ($laundryOrders as $laundryOrder) {
            $schedules = $this->laundryOrderService->composeSchedules($laundryOrder);
            foreach ($schedules as $schedule) {
                $this->createSchedule($schedule, $laundryOrder);
            }
        }
    }

    private function createSchedule(BuildScheduleDTO $data, LaundryOrder $laundryOrder)
    {
        $employees = User::whereHas('teams', function ($query) use ($data) {
            $query->where('id', $data->team_id);
        })->get();

        $employeeIds = $employees->map(fn ($user) => ['user_id' => $user->id])->toArray();

        // get discount percentage
        /** @var CustomerDiscount|null $discount */
        $discount = $laundryOrder->user->laundryDiscounts()->first();
        $discountPercentage = $discount ? $discount->value : 0;

        $items = $laundryOrder->products->map(fn ($product) => [
            'itemable_id' => $product->product_id,
            'itemable_type' => Product::class,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'discount_percentage' => $discountPercentage,
        ]);

        DB::transaction(function () use ($employeeIds, $items, $laundryOrder, $data) {
            $scheduleLaundry = ScheduleLaundry::create([
                'laundry_order_id' => $laundryOrder->id,
                'type' => $data->type,
            ]);
            $schedule = Schedule::create([
                ...$data->toArray(),
                'is_fixed' => false,
                'original_start_at' => $data->start_at,
                'scheduleable_id' => $scheduleLaundry->id,
                'scheduleable_type' => ScheduleLaundry::class,
            ]);
            $schedule->scheduleEmployees()->createMany($employeeIds);
            $schedule->items()->createMany($items);
        });
    }
}
