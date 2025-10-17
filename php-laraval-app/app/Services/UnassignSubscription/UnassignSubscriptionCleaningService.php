<?php

namespace App\Services\UnassignSubscription;

use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Subscription\UpdateSubscriptionRequestDTO;
use App\DTOs\UnassignSubscription\UpdateUnassignSubscriptionRequestDTO;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Exceptions\ErrorResponseException;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\UnassignSubscription;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;

class UnassignSubscriptionCleaningService
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
        $detail = $request->cleaning_detail;

        if ($detail->isOptional('team_id') || ! $detail->team_id) {
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

        $teamMembers = Team::find($detail->team_id)->users()->count();

        $endTime = calculate_end_time(
            $detail->start_time,
            calculate_calendar_quarters(
                $detail->quarters,
                $teamMembers
            )
        );

        $this->assignEndDateTime($request, $endTime);
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
        $collidedSchedules = $this->subscriptionService->checkCollision(
            $request->cleaning_detail->team_id,
            $request->frequency,
            $request->start_at,
            $request->cleaning_detail->start_time,
            $request->end_at,
            $request->cleaning_detail->end_time,
            null,
            $timezone,
        );

        if ($collidedSchedules->isNotEmpty()) {
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

    /**
     * Check if the subscription's schedules should be replaced.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     * @return bool
     */
    public function shouldReplaceSchedules($request, $subscription)
    {
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;

        return ! ($detail->team_id === $request->cleaning_detail->team_id &&
            $subscription->frequency === $request->frequency &&
            $subscription->start_at->format('Y-m-d') === $request->start_at &&
            $subscription->end_at?->format('Y-m-d') === $request->end_at &&
            $detail->start_time === $request->cleaning_detail->start_time &&
            $detail->end_time === $request->cleaning_detail->end_time
        );
    }

    /**
     * Fill value end at and end time at.
     *
     * @param  UpdateUnassignSubscriptionRequestDTO  $request
     * @param  string  $endTime
     */
    private function assignEndDateTime($request, $endTime)
    {
        if ($request->frequency === SubscriptionFrequencyEnum::Once()) {
            $carbonEndAt = Carbon::createFromDate($request->start_at)->addDays(
                $request->cleaning_detail->start_time > $endTime ? 1 : 0
            );

            $request->end_at = $carbonEndAt->format('Y-m-d');
        }

        $request->cleaning_detail->end_time = $endTime;
    }
}
