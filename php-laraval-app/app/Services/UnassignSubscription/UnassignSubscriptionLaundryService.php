<?php

namespace App\Services\UnassignSubscription;

use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\UnassignSubscription\UpdateUnassignSubscriptionRequestDTO;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Exceptions\ErrorResponseException;
use App\Models\LaundryPreference;
use App\Models\UnassignSubscription;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;

class UnassignSubscriptionLaundryService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {
    }

    /**
     * Preparation process from request.
     *
     * @param  UpdateUnassignSubscriptionRequestDTO  $request
     * @param  UnassignSubscription  $unassignSubscription
     */
    public function preprocess($request, $unassignSubscription)
    {
        $detail = $request->laundry_detail;

        if ($detail->isOptional('pickup_team_id') || ! $detail->pickup_team_id) {
            throw new ErrorResponseException(
                __('team is required'),
            );
        }

        $request->assignOptionalValues([
            'user_id' => $unassignSubscription->user_id,
            'service_id' => $unassignSubscription->service_id,
            'product_carts' => $unassignSubscription->product_carts,
            'addon_ids' => [],
            'description' => $unassignSubscription->description,
            'is_fixed' => $unassignSubscription->is_fixed,
            'frequency' => $unassignSubscription->frequency,
            'start_at' => $unassignSubscription->start_at,
            'fixed_price' => $unassignSubscription->fixed_price,
        ]);

        if ($request->frequency === SubscriptionFrequencyEnum::Once()) {
            $carbonEndAt = Carbon::createFromDate($request->start_at);

            $request->end_at = $carbonEndAt->format('Y-m-d');
        }
    }

    /**
     * Check if the subscription will collide with other schedules.
     *
     * @param  UpdateUnassignSubscriptionRequestDTO  $request
     * @param  string  $timezone
     * @param  bool  $sendPayload
     */
    public function checkCollision(
        $request,
        $sendPayload = true,
        $timezone = 'Europe/Stockholm',
    ) {
        // Check pickup collission with other schedules
        $pickupStartTime = $request->laundry_detail->pickup_time;
        $pickupEndTime = calculate_end_time($pickupStartTime, 1);

        $collidedSchedules = $this->subscriptionService->checkCollision(
            $request->laundry_detail->pickup_team_id,
            $request->frequency,
            $request->start_at,
            $pickupStartTime,
            $request->end_at,
            $pickupEndTime,
            null,
            $timezone,
        );

        $deliveryTeamId = $request->laundry_detail->delivery_team_id;
        if ($deliveryTeamId) {
            // Check delivery collission with other schedules
            $pickupStratDate = $request->start_at;
            $deliveryStratTime = $request->laundry_detail->delivery_time;
            $laundryPreferenceId = $request->laundry_detail->laundry_preference_id;
            $deliveryEndTime = calculate_end_time($deliveryStratTime, 1);
            $deliveryStratDate = $this->getDeliveryStartDate($laundryPreferenceId, $pickupStratDate);

            $collidedDeliverySchedules = $this->subscriptionService->checkCollision(
                $deliveryTeamId,
                $request->frequency,
                $deliveryStratDate->format('Y-m-d'),
                $deliveryStratTime,
                $request->end_at,
                $deliveryEndTime,
                null,
                $timezone,
            );

            // merge collided schedules
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
            includes: ['subscription.user', 'team'],
            onlys: [
                'id',
                'subscription.user.fullname',
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
}
