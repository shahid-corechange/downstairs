<?php

namespace App\Jobs;

use App\DTOs\Subscription\SubscriptionLaundryOrderDTO;
use App\Models\LaundryOrder;
use App\Models\SubscriptionItem;
use App\Services\Schedule\ScheduleLaundryService;
use App\Services\Schedule\ScheduleService;
use DB;

/**
 * Create a laundry order and schedule
 */
class CreateLaundryOrderJob extends BaseJob
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
        protected SubscriptionLaundryOrderDTO $data
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
            $subscription = $this->data->subscription;
            /** @var \App\Models\SubscriptionLaundryDetail $detail */
            $detail = $subscription->subscribable;
            $timezone = 'Europe/Stockholm';
            $subscriptionPickupStart = $subscription->start_at
                ->copy()
                ->setTimeFromTimeString($detail->pickup_time)
                ->setTimezone($timezone);

            [$pickupStart, $pickupEnd] = ScheduleService::normalizeTime(
                $subscriptionPickupStart,
                $this->data->pickup_schedule->start_at,
                $this->data->pickup_schedule->end_at,
            );

            [$deliveryStart, $deliveryEnd] = ScheduleService::normalizeTime(
                $subscriptionPickupStart,
                $this->data->delivery_schedule->start_at,
                $this->data->delivery_schedule->end_at,
            );

            $pickupEmployees = $service->getEmployees($this->data->pickup_schedule->team_id);
            $deliveryEmployees = $service->getEmployees($this->data->delivery_schedule->team_id);

            $items = $subscription->items
                ->map(fn (SubscriptionItem $item) => [
                    'itemable_id' => $item->itemable_id,
                    'itemable_type' => $item->itemable_type,
                    'price' => $item->itemable->price,
                    'quantity' => $item->quantity,
                    'discount_percentage' => 0,
                ]);

            DB::transaction(function () use (
                $service,
                $pickupStart,
                $pickupEnd,
                $deliveryStart,
                $deliveryEnd,
                $pickupEmployees,
                $deliveryEmployees,
                $items,
                $detail,
            ) {
                // Create order
                $laundryOrder = LaundryOrder::create($detail->toArray());

                // Create schedule pickup
                $service->storeSchedule(
                    $this->data->pickup_schedule,
                    $laundryOrder,
                    $pickupStart,
                    $pickupEnd,
                    $pickupEmployees,
                    $items,
                );

                // Create schedule delivery
                $service->storeSchedule(
                    $this->data->delivery_schedule,
                    $laundryOrder,
                    $deliveryStart,
                    $deliveryEnd,
                    $deliveryEmployees,
                    $items,
                );
            });
        });
    }
}
