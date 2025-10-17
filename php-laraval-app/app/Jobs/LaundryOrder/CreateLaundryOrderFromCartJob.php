<?php

namespace App\Jobs\LaundryOrder;

use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Jobs\BaseJob;
use App\Models\Addon;
use App\Models\LaundryOrder;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\SubscriptionLaundryDetail;
use App\Services\LaundryOrder\LaundryOrderHistoryService;
use App\Services\LaundryPreferenceService;
use App\Services\Schedule\ScheduleNoteService;
use App\Services\Schedule\ScheduleTaskService;
use DB;
use Illuminate\Database\Eloquent\Builder;

class CreateLaundryOrderFromCartJob extends BaseJob
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
        protected array $laundryScheduleIds,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(LaundryOrderHistoryService $historyService): void
    {
        $this->handleWrapper(function () use ($historyService) {
            $usedScheduleIds = $this->laundryScheduleIds;
            $schedules = Schedule::whereIn('id', $this->laundryScheduleIds)->get();

            foreach ($schedules as $schedule) {
                // select service for subscription
                $serviceId = $schedule->customer->membership_type === MembershipTypeEnum::Company() ?
                    config('downstairs.services.laundry.company.id') :
                    config('downstairs.services.laundry.private.id');

                $deliverySchedule = Schedule::future()
                    ->whereNotIn('id', $usedScheduleIds)
                    ->where('user_id', $schedule->user_id)
                    ->where('scheduleable_type', ScheduleCleaning::class)
                    ->whereHas('scheduleable', function (Builder $query) {
                        $query->whereNull('laundry_order_id');
                    })
                    ->orderBy('start_at')
                    ->first();

                $usedScheduleIds[] = $deliverySchedule->id;

                $laundryPreference = LaundryPreferenceService::getPreference(
                    $schedule->start_at,
                    $deliverySchedule->start_at,
                );

                DB::transaction(function () use (
                    $schedule,
                    $serviceId,
                    $deliverySchedule,
                    $historyService,
                    $laundryPreference,
                ) {
                    // Initialize subscription to be able use fixed price
                    $subscriptionDetail = SubscriptionLaundryDetail::create([
                        'store_id' => config('downstairs.laundry.mainStore.id'),
                        'laundry_preference_id' => $laundryPreference->id,
                        'pickup_time' => $schedule->start_at->copy()->format('H:i:s'),
                    ]);

                    $subscription = Subscription::create([
                        'user_id' => $schedule->user_id,
                        'customer_id' => $schedule->customer_id,
                        'service_id' => $serviceId,
                        'frequency' => SubscriptionFrequencyEnum::Once(),
                        'subscribable_type' => SubscriptionLaundryDetail::class,
                        'subscribable_id' => $subscriptionDetail->id,
                        'start_at' => $schedule->start_at->copy()->format('Y-m-d'),
                        'end_at' => $schedule->end_at->copy()->format('Y-m-d'),
                        'status' => SubscriptionStatusEnum::Active(),
                    ]);

                    $laundryOrder = LaundryOrder::create([
                        'store_id' => config('downstairs.laundry.mainStore.id'),
                        'laundry_preference_id' => $laundryPreference->id,
                        'ordered_at' => $schedule->start_at,
                        'payment_method' => PaymentMethodEnum::Invoice(),
                        'status' => LaundryOrderStatusEnum::Pending(),
                        'user_id' => $schedule->user_id,
                        'customer_id' => $schedule->customer_id,
                        'causer_id' => $schedule->user_id,
                        'subscription_id' => $subscription->id,
                    ]);

                    $historyService->addCreateHistory($laundryOrder);

                    // add pickup schedule to laundry order
                    $schedule->scheduleable()->update([
                        'laundry_order_id' => $laundryOrder->id,
                        'laundry_type' => ScheduleLaundryTypeEnum::Pickup(),
                    ]);

                    ScheduleTaskService::addLaundryTask(
                        $schedule,
                        $laundryOrder,
                        ScheduleLaundryTypeEnum::Pickup(),
                    );
                    ScheduleNoteService::addLaundryNote(
                        $schedule,
                        $laundryOrder,
                        ScheduleLaundryTypeEnum::Pickup(),
                    );

                    // add delivery schedule to laundry order
                    $deliverySchedule->scheduleable()->update([
                        'laundry_order_id' => $laundryOrder->id,
                        'laundry_type' => ScheduleLaundryTypeEnum::Delivery(),
                    ]);

                    $deliverySchedule->items()->create([
                        'itemable_id' => config('downstairs.addons.laundry.id'),
                        'itemable_type' => Addon::class,
                        'price' => 0,
                        'quantity' => 1,
                        'discount_percentage' => 0,
                        'payment_method' => PaymentMethodEnum::Invoice(),
                    ]);

                    ScheduleTaskService::addLaundryTask(
                        $deliverySchedule,
                        $laundryOrder,
                        ScheduleLaundryTypeEnum::Delivery(),
                    );
                    ScheduleNoteService::addLaundryNote(
                        $deliverySchedule,
                        $laundryOrder,
                        ScheduleLaundryTypeEnum::Delivery(),
                    );
                });
            }
        });
    }
}
