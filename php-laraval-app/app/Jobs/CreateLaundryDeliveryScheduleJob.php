<?php

namespace App\Jobs;

use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Models\LaundryOrder;
use App\Models\SubscriptionItem;
use App\Services\Schedule\ScheduleLaundryService;
use App\Services\Schedule\ScheduleService;
use DB;

class CreateLaundryDeliveryScheduleJob extends BaseJob
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
        protected LaundryOrder $order,
        protected SubscriptionScheduleDTO $data
    ) {
        $this->queue = 'schedules';
    }

    /**
     * Execute the job.
     */
    public function handle(
        ScheduleLaundryService $service,
    ): void {
        $this->handleWrapper(function () use ($service) {
            $subscription = $this->order->subscription;
            $subscriptionPickupStart = $subscription->start_at
                ->copy()
                ->setTimeFromTimeString($subscription->subscribable->delivery_time)
                ->setTimezone('Europe/Stockholm');

            [$startAt, $endAt] = ScheduleService::normalizeTime(
                $subscriptionPickupStart,
                $this->data->start_at,
                $this->data->end_at,
            );

            $employees = $service->getEmployees($this->data->team_id);

            $items = $subscription->items
                ->map(fn (SubscriptionItem $item) => [
                    'itemable_id' => $item->itemable_id,
                    'itemable_type' => $item->itemable_type,
                    'price' => $item->itemable->price,
                    'quantity' => $item->quantity,
                    'discount_percentage' => 0,
                ]);

            DB::transaction(function () use ($service, $employees, $items, $startAt, $endAt) {
                $service->storeSchedule(
                    $this->data,
                    $this->order,
                    $startAt,
                    $endAt,
                    $employees,
                    $items,
                );
            });
        });
    }
}
