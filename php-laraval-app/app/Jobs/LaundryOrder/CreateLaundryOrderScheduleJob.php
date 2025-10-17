<?php

namespace App\Jobs\LaundryOrder;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\DTOs\Schedule\BuildScheduleDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Jobs\BaseJob;
use App\Jobs\SendNotificationJob;
use App\Models\CustomerDiscount;
use App\Models\LaundryOrder;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleLaundry;
use App\Models\User;
use Carbon\Carbon;
use DB;

class CreateLaundryOrderScheduleJob extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected BuildScheduleDTO $data,
        protected LaundryOrder $laundryOrder,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->handleWrapper(function () {
            $employees = User::whereHas('teams', function ($query) {
                $query->where('id', $this->data->team_id);
            })->get();

            $employeeIds = $employees->map(fn ($user) => ['user_id' => $user->id])->toArray();

            // get discount percentage
            /** @var CustomerDiscount|null $discount */
            $discount = $this->laundryOrder->user->laundryDiscounts()->first();
            $discountPercentage = $discount ? $discount->value : 0;

            $items = $this->laundryOrder->products->map(fn ($product) => [
                'itemable_id' => $product->product_id,
                'itemable_type' => Product::class,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'discount_percentage' => $discountPercentage,
            ]);

            $schedule = DB::transaction(function () use ($employeeIds, $items) {
                $scheduleLaundry = ScheduleLaundry::create([
                    'laundry_order_id' => $this->laundryOrder->id,
                    'type' => $this->data->type,
                ]);
                $schedule = Schedule::create([
                    ...$this->data->toArray(),
                    'is_fixed' => false,
                    'original_start_at' => $this->data->start_at,
                    'scheduleable_id' => $scheduleLaundry->id,
                    'scheduleable_type' => ScheduleLaundry::class,
                ]);

                $schedule->items()->createMany($items);

                // if team is set, assign employees to the schedule
                if ($schedule->team_id) {
                    $schedule->scheduleEmployees()->createMany($employeeIds);
                }

                return $schedule;
            });

            $startAt = Carbon::parse($this->data->start_at)
                ->copy()
                ->timezone('Europe/Stockholm');

            // generate notification for employees in the team
            if ($schedule->team_id) {
                foreach ($employees as $employee) {
                    $this->sendNotification($employee, $schedule, $startAt);
                }
            }
        });
    }

    /**
     * Send notification to employee
     *
     * @param  User  $employee
     * @param  Schedule  $schedule
     * @param  Carbon  $startAt
     */
    private function sendNotification($employee, $schedule, $startAt)
    {
        scoped_localize($employee->info->language, function () use ($employee, $schedule, $startAt) {
            SendNotificationJob::dispatch(
                $employee,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Employee(),
                        NotificationTypeEnum::ScheduleUpdated(),
                        __('notification title schedule created'),
                        __('notification body schedule created', [
                            'worker' => $employee->first_name,
                            'date' => $startAt->format('Y-m-d'),
                            'time' => $startAt->format('H:i'),
                        ]),
                        NotificationSchedulePayloadDTO::from([
                            'id' => $schedule->id,
                            'start_at' => $this->data->start_at,
                        ])->toArray(),
                    ),
                    shouldSave: true,
                ),
            );
        });
    }
}
