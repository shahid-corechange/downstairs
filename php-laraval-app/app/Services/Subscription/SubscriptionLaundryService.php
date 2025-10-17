<?php

namespace App\Services\Subscription;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Subscription\SubscriptionProductRequestDTO;
use App\DTOs\Subscription\SubscriptionWizardRequestDTO;
use App\DTOs\Subscription\UpdateSubscriptionRequestDTO;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Exceptions\ErrorResponseException;
use App\Models\LaundryPreference;
use App\Models\Subscription;
use App\Models\SubscriptionLaundryDetail;
use Carbon\Carbon;

class SubscriptionLaundryService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionLaundryScheduleService $scheduleService,
    ) {
    }

    /**
     *  Fill missing or optional data from request.
     *
     * @param  SubscriptionWizardRequestDTO|UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    public function populates($request, $subscription)
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;

        $request->assignOptionalValues([
            'frequency' => $subscription->frequency,
            'start_at' => $subscription->start_at->format('Y-m-d'),
            'end_at' => $subscription->end_at?->format('Y-m-d'),
            'is_fixed' => $subscription->is_fixed,
            'description' => $subscription->description,
            'products' => SubscriptionProductRequestDTO::collection($subscription->products),
        ]);

        $request->laundry_detail->assignOptionalValues([
            'laundry_preference_id' => $detail->laundry_preference_id,
            'pickup_property_id' => $detail->pickup_property_id,
            'pickup_team_id' => $detail->pickup_team_id,
            'pickup_time' => $detail->pickup_time,
        ]);
    }

    /**
     * Preparation process from request.
     *
     * @param  SubscriptionWizardRequestDTO|UpdateSubscriptionRequestDTO  $request
     */
    public function preprocess($request)
    {
        if ($request->frequency === SubscriptionFrequencyEnum::Once()) {
            $carbonEndAt = Carbon::createFromDate($request->start_at);

            $request->end_at = $carbonEndAt->format('Y-m-d');
        }

        $request->assignIfOptional('addon_ids', []);
    }

    /**
     *  Create subscription and laundry detail.
     *
     * @param  BaseData  $request
     * @return Subscription
     */
    public function create($request)
    {
        $data = $request->toArray();

        $detail = SubscriptionLaundryDetail::create($data['laundry_detail']);
        $subscription = Subscription::create([
            ...$data,
            'subscribable_type' => SubscriptionLaundryDetail::class,
            'subscribable_id' => $detail->id,
        ]);

        return $subscription;
    }

    /**
     *  Update subscription and laundry schedules.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     * @param  bool  $isFixed
     * @param  string  $descripton
     */
    public function update(
        $request,
        $subscription,
        $isFixed,
        $descripton,
    ) {
        $data = $request->toArray();

        $subscription->update($data);
        $subscription->subscribable()->update($data['laundry_detail']);
        $isReplace = $this->shouldReplaceSchedules($request, $subscription);

        if ($isReplace && ! $subscription->is_paused) {
            $this->replace($request, $subscription);
        } elseif (($request->is_fixed !== $isFixed ||
            $request->description !== $descripton) &&
            ! $subscription->is_paused) {
            $subscription->schedules()
                ->booked()
                ->update([
                    'is_fixed' => $request->is_fixed,
                    'note->subscription_note' => $request->description,
                ]);
        }
    }

    /**
     *  Replace laudry schedules based on new data.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    public function replace($request, $subscription)
    {
        // If pickup schedules should be replaced, replace laundry orders.
        if ($this->shouldReplacePickupSchedules($request, $subscription)) {
            /** @var \App\Models\SubscriptionLaundryDetail $detail */
            $detail = $subscription->subscribable;
            $isContinue = $this->shouldContinuePickupSchedules($request, $subscription);
            $orders = $subscription->futureLaundryOrders();

            // If pickup team request is same as existing, don't replace first schedule.
            // To avoid missing schedule when find original start at previous schedule.
            if ($request->laundry_detail->pickup_team_id === $detail->pickup_team_id) {
                $startAt = $subscription->start_at->copy()->setTimeFromTimeString($detail->pickup_time);

                $orders->whereHas('schedule', function ($query) use ($startAt) {
                    $query->where('type', ScheduleLaundryTypeEnum::Pickup())
                        ->whereNot('original_start_at', $startAt);
                });
            }

            // Delete all future orders and schedules
            $orders->forceDelete();

            if ($isContinue) {
                // Create next schedules based on the new frequency.
                $this->createNextSchedules($subscription);
            } else {
                // Create initial schedules based on the new request.
                $this->createInitialSchedules($subscription);
            }
        } elseif ($this->shouldReplaceDeliverySchedules($request, $subscription)) {
            $this->replaceDelivery($subscription);
        }
    }

    /**
     * Soft delete subscription and remove laudry schedules from database.
     *
     * @param  Subscription  $subscription
     */
    public function remove($subscription)
    {
        $subscription->futureLaundryOrders()->forceDelete();
    }

    /**
     * Check if the subscription will collide with other schedules.
     *
     * @param  SubscriptionWizardRequestDTO|UpdateSubscriptionRequestDTO  $request
     * @param  Subscription|null  $subscription
     * @param  int|null  $excludeId
     * @param  string  $timezone
     * @param  bool  $sendPayload
     */
    public function checkCollision(
        $request,
        $subscription,
        $sendPayload = true,
        $timezone = 'Europe/Stockholm',
    ) {
        // Check pickup collission with other schedules
        $pickupStartTime = $request ? $request->laundry_detail->pickup_time : $subscription->subscribable->pickup_time;
        $pickupEndTime = calculate_end_time(
            $pickupStartTime,
            config('downstairs.schedule.laundry.quarters')
        );
        $endAt = $request ? $request->end_at : ($subscription->end_at ? $subscription->end_at->format('Y-m-d') : null);

        $collidedSchedules = $this->subscriptionService->checkCollision(
            $request ? $request->laundry_detail->pickup_team_id : $subscription->subscribable->pickup_team_id,
            $request ? $request->frequency : $subscription->frequency,
            $request ? $request->start_at : $subscription->start_at->format('Y-m-d'),
            $pickupStartTime,
            $endAt,
            $pickupEndTime,
            $request ? null : $subscription->id,
            $timezone,
        );

        $deliveryId = $request ? $request->laundry_detail->delivery_property_id :
            $subscription->subscribable->delivery_property_id;

        if ($deliveryId) {
            // Check delivery collission with other schedules
            $pickupStratDate = $request ? $request->start_at : $subscription->start_at->format('Y-m-d');
            $deliveryStartTime = $request ? $request->laundry_detail->delivery_time :
                $subscription->subscribable->delivery_time;
            $laundryPreferenceId = $request ? $request->laundry_detail->laundry_preference_id :
                $subscription->subscribable->laundry_preference_id;
            $deliveryEndTime = calculate_end_time($deliveryStartTime, 1);
            $deliveryStratDate = $this->getDeliveryStartDate($laundryPreferenceId, $pickupStratDate);

            $collidedDeliverySchedules = $this->subscriptionService->checkCollision(
                $deliveryId,
                $request ? $request->frequency : $subscription->frequency,
                $deliveryStratDate->format('Y-m-d'),
                $deliveryStartTime,
                $request ? $request->end_at : $subscription->end_at->format('Y-m-d'),
                $deliveryEndTime,
                $request ? null : $subscription->id,
                $timezone,
            );

            // combine pickup and delivery schedules
            $collidedSchedules = $collidedSchedules->merge($collidedDeliverySchedules);
        }

        if ($collidedSchedules->isNotEmpty()) {
            $this->throwCollisionError($collidedSchedules, $sendPayload);
        }
    }

    /**
     * Get delivery start date based on laundry preference.
     *
     * @param  int  $laundryPreferenceId
     * @param  string  $pickupStartDate
     * @return Carbon
     */
    public function getDeliveryStartDate($laundryPreferenceId, $pickupStartDate)
    {
        $laundryPreference = LaundryPreference::find($laundryPreferenceId);

        return Carbon::createFromDate($pickupStartDate)->addHours($laundryPreference->hours);
    }

    /**
     * Throw collison error.
     *
     * @param  \Illuminate\Support\Collection<string|int, \App\Models\ScheduleCleaning>  $collidedSchedules
     * @param  bool  $sendPayload
     */
    private function throwCollisionError($collidedSchedules, $sendPayload)
    {
        $errorPayload = $sendPayload ? ScheduleResponseDTO::transformCollection(
            $collidedSchedules,
            includes: ['detail.subscription.user', 'team'],
            onlys: [
                'id',
                'detail.subscription.user.fullname',
                'team.name',
                'teamId',
                'startAt',
                'endAt',
            ]
        ) : null;

        throw new ErrorResponseException(
            __('schedules collisions'),
            errors: [
                'errorPayload' => $errorPayload,
            ],
        );
    }

    /**
     * Create the initial schedules for a subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $scheduleNotQueue
     */
    public function createInitialSchedules($subscription, $scheduleNotQueue = 5)
    {
        $this->scheduleService->createInitialSchedules($subscription, $scheduleNotQueue);
    }

    /**
     * Create the next schedules for a subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $scheduleNotQueue
     */
    public function createNextSchedules($subscription, $scheduleNotQueue = 5)
    {
        $this->scheduleService->createNextSchedules($subscription, $scheduleNotQueue);
    }

    /**
     * Check if pickup schedules should continue.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     * @return bool
     */
    public function shouldReplaceSchedules($request, $subscription)
    {
        return $this->shouldReplacePickupSchedules($request, $subscription) ||
            $this->shouldReplaceDeliverySchedules($request, $subscription);
    }

    /**
     *  Replace delivery schedules based on new subscription data.
     *
     * @param  Subscription  $subscription
     */
    private function replaceDelivery($subscription)
    {
        $deliveries = $subscription->schedules()
            ->whereHas('schedulable', function ($query) {
                $query->where('type', ScheduleLaundryTypeEnum::Delivery());
            })
            ->future();

        // Get the laundry orders from the deliveries.
        $deliveryIds = $deliveries->pluck('id')->toArray();
        $laundryOrders = $subscription->laundryOrders()
            ->whereHas('schedules', function ($query) use ($deliveryIds) {
                $query->whereIn('id', $deliveryIds);
            });

        $deliveries->forceDelete();

        $this->scheduleService->dispatchDeliverySchedules($laundryOrders);
    }

    /**
     * Check if pickup schedules should continue.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     * @return bool
     */
    private function shouldContinuePickupSchedules($request, $subscription)
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;

        return $subscription->start_date->format('Y-m-d') === $request->start_at &&
            $subscription->end_date?->format('Y-m-d') === $request->end_at &&
            $detail->pickup_time === $request->laundry_detail->pickup_time &&
            $detail->pickup_team_id === $request->laundry_detail->pickup_team_id;
    }

    /**
     * Check if the subscription's pickup schedules should be replaced.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    private function shouldReplacePickupSchedules($request, $subscription): bool
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;

        return ! ($subscription->frequency === $request->frequency &&
            $subscription->start_date->format('Y-m-d') === $request->start_at &&
            $subscription->end_date?->format('Y-m-d') === $request->end_at &&
            $detail->pickup_time === $request->laundry_detail->pickup_time &&
            $detail->pickup_team_id === $request->laundry_detail->pickup_team_id
        );
    }

    /**
     * Check if the subscription's delivery schedules should be replaced.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    private function shouldReplaceDeliverySchedules($request, $subscription): bool
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;

        return ! ($detail->laundry_preference_id === $request->laundry_detail->laundry_preference_id &&
            $subscription->frequency === $request->frequency &&
            $subscription->start_date->format('Y-m-d') === $request->start_at &&
            $subscription->end_date?->format('Y-m-d') === $request->end_at &&
            $detail->delivery_time === $request->laundry_detail->delivery_time &&
            $detail->delivery_team_id === $request->laundry_detail->delivery_team_id
        );
    }
}
