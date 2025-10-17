<?php

namespace App\Jobs\LaundryOrder;

use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
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
use App\Models\User;
use App\Services\LaundryOrder\LaundryOrderHistoryService;
use App\Services\LaundryPreferenceService;
use App\Services\Schedule\ScheduleNoteService;
use App\Services\Schedule\ScheduleTaskService;
use DB;
use Illuminate\Database\Eloquent\Builder;

class CreateLaundryOrderFromScheduleJob extends BaseJob
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
        protected Schedule $schedule,
        protected ?User $causer = null,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(LaundryOrderHistoryService $historyService): void
    {
        $this->handleWrapper(function () use ($historyService) {
            // select service for subscription
            $serviceId = $this->schedule->customer->membership_type === MembershipTypeEnum::Company() ?
                config('downstairs.services.laundry.company.id') :
                config('downstairs.services.laundry.private.id');

            $deliverySchedule = Schedule::whereNot('id', $this->schedule->id)
                ->where('customer_id', $this->schedule->customer_id)
                ->where('scheduleable_type', ScheduleCleaning::class)
                ->where('status', ScheduleStatusEnum::Booked())
                ->whereHas('scheduleable', function (Builder $query) {
                    $query->whereNull('laundry_order_id');
                })
                ->where('start_at', '>=', $this->schedule->start_at)
                ->orderBy('start_at')
                ->first();

            $laundryPreference = LaundryPreferenceService::getPreference(
                $this->schedule->start_at,
                $deliverySchedule->start_at,
            );

            DB::transaction(function () use (
                $serviceId,
                $deliverySchedule,
                $historyService,
                $laundryPreference,
            ) {
                // Initialize subscription to be able use fixed price
                $subscriptionDetail = SubscriptionLaundryDetail::create([
                    'store_id' => config('downstairs.laundry.mainStore.id'),
                    'laundry_preference_id' => $laundryPreference->id,
                    'pickup_time' => $this->schedule->start_at->copy()->format('H:i:s'),
                ]);

                $subscription = Subscription::create([
                    'user_id' => $this->schedule->user_id,
                    'customer_id' => $this->schedule->customer_id,
                    'service_id' => $serviceId,
                    'frequency' => SubscriptionFrequencyEnum::Once(),
                    'subscribable_type' => SubscriptionLaundryDetail::class,
                    'subscribable_id' => $subscriptionDetail->id,
                    'start_at' => $this->schedule->start_at->copy()->format('Y-m-d'),
                    'end_at' => $this->schedule->end_at->copy()->format('Y-m-d'),
                    'status' => SubscriptionStatusEnum::Active(),
                ]);

                $laundryOrder = LaundryOrder::create([
                    'store_id' => config('downstairs.laundry.mainStore.id'),
                    'laundry_preference_id' => $laundryPreference->id,
                    'ordered_at' => $this->schedule->start_at,
                    'payment_method' => PaymentMethodEnum::Invoice(),
                    'status' => LaundryOrderStatusEnum::Pending(),
                    'user_id' => $this->schedule->user_id,
                    'customer_id' => $this->schedule->customer_id,
                    'causer_id' => $this->schedule->user_id,
                    'subscription_id' => $subscription->id,
                ]);

                $historyService->addCreateHistory($laundryOrder, causer: $this->causer);

                // add pickup schedule to laundry order
                $this->schedule->scheduleable()->update([
                    'laundry_order_id' => $laundryOrder->id,
                    'laundry_type' => ScheduleLaundryTypeEnum::Pickup(),
                ]);

                ScheduleTaskService::addLaundryTask(
                    $this->schedule,
                    $laundryOrder,
                    ScheduleLaundryTypeEnum::Pickup(),
                );
                ScheduleNoteService::addLaundryNote(
                    $this->schedule,
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
        });
    }
}
