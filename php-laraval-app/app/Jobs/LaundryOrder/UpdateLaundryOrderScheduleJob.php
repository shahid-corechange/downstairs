<?php

namespace App\Jobs\LaundryOrder;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Jobs\BaseJob;
use App\Jobs\SendNotificationJob;
use App\Models\LaundryOrder;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;

class UpdateLaundryOrderScheduleJob extends BaseJob
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
        protected LaundryOrder $laundryOrder,
        protected Schedule $schedule,
        protected Carbon $startAt,
        protected Carbon $endAt,
        protected int $propertyId,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->handleWrapper(function () {
            /** @var Collection<int, User> $employees */
            $employees = $this->schedule->scheduleEmployees->map(fn ($scheduleEmployee) => $scheduleEmployee->user);
            $oldStartAt = $this->schedule->start_at;
            $oldEndAt = $this->schedule->end_at;

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

            DB::transaction(function () use ($items) {
                $this->schedule->update([
                    'property_id' => $this->propertyId,
                    'start_at' => $this->startAt,
                    'end_at' => $this->endAt,
                ]);
                $this->schedule->syncItems($items->toArray());
            });

            if ($this->schedule->team_id &&
                ($oldStartAt !== $this->startAt || $oldEndAt !== $this->endAt)) {
                $startAt = $this->startAt->copy()->timezone('Europe/Stockholm');
                foreach ($employees as $employee) {
                    $this->sendNotification($employee, $this->schedule, $startAt);
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
                        __('notification title schedule updated'),
                        __('notification body schedule updated', [
                            'worker' => $employee->first_name,
                            'date' => $startAt->format('Y-m-d'),
                            'time' => $startAt->format('H:i'),
                        ]),
                        NotificationSchedulePayloadDTO::from([
                            'id' => $schedule->id,
                            'start_at' => $startAt,
                        ])->toArray(),
                    ),
                    shouldSave: true,
                ),
            );
        });
    }
}
