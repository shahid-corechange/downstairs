<?php

namespace App\Services\Subscription;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Subscription\SubscriptionProductRequestDTO;
use App\DTOs\Subscription\SubscriptionWizardRequestDTO;
use App\DTOs\Subscription\UpdateSubscriptionRequestDTO;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Exceptions\ErrorResponseException;
use App\Models\Subscription;
use App\Models\SubscriptionCleaningDetail;
use App\Models\Team;
use Carbon\Carbon;

class SubscriptionCleaningService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionCleaningScheduleService $scheduleService,
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
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;

        $request->assignOptionalValues([
            'frequency' => $subscription->frequency,
            'start_at' => $subscription->start_at->format('Y-m-d'),
            'end_at' => $subscription->end_at?->format('Y-m-d'),
            'is_fixed' => $subscription->is_fixed,
            'description' => $subscription->description,
            'products' => SubscriptionProductRequestDTO::collection($subscription->products),
        ]);

        $request->cleaning_detail->assignOptionalValues([
            'team_id' => $detail->team_id,
            'start_time' => $detail->start_time,
            'quarters' => $detail->quarters,
        ]);
    }

    /**
     * Preparation process from request.
     *
     * @param  SubscriptionWizardRequestDTO|UpdateSubscriptionRequestDTO  $request
     */
    public function preprocess($request)
    {
        $teamMembers = Team::find($request->cleaning_detail->team_id)->users()->count();

        $endTime = calculate_end_time(
            $request->cleaning_detail->start_time,
            calculate_calendar_quarters(
                $request->cleaning_detail->quarters,
                $teamMembers
            )
        );

        $this->assignEndDateTime($request, $endTime);
        $request->assignIfOptional('addon_ids', []);
    }

    /**
     *  Create subscription and cleaning detail.
     *
     * @param  BaseData  $request
     * @return Subscription
     */
    public function create($request)
    {
        $data = $request->toArray();

        $detail = SubscriptionCleaningDetail::create($data['cleaning_detail']);
        $subscription = Subscription::create([
            ...$data,
            'subscribable_type' => SubscriptionCleaningDetail::class,
            'subscribable_id' => $detail->id,
        ]);

        return $subscription;
    }

    /**
     *  Update subscription and schedule cleanings.
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
        $subscription->subscribable()->update($data['cleaning_detail']);
        $isReplace = $this->shouldReplaceSchedules($request, $subscription);

        if ($isReplace && ! $subscription->is_paused) {
            $this->replace($request, $subscription);
        } elseif (($request->is_fixed !== $isFixed ||
            $request->description !== $descripton) &&
            ! $subscription->is_paused) {
            $subscription->schedules()
                ->active()
                ->update([
                    'is_fixed' => $request->is_fixed,
                    'note->subscription_note' => $request->description,
                ]);
        }
    }

    /**
     *  Replace schedule cleanings based on new data.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    public function replace($request, $subscription)
    {
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;
        $cleanings = $subscription->schedules()->future();
        $isContinue = $this->shouldContinueSchedules($request, $subscription);

        // If team request is same as existing, don't replace first schedule.
        // To avoid missing schedule when find original start at previous schedule.
        if ($request->cleaning_detail->team_id === $detail->team_id) {
            $cleanings->whereNot(
                'original_start_at',
                $subscription->start_at
                    ->copy()
                    ->setTimeFromTimeString($detail->start_time)
            );
        }

        // Delete all future schedules
        $cleanings->forceDelete();

        if ($isContinue) {
            // Create next schedules based on the new frequency.
            $this->createNextSchedules($subscription);
        } else {
            // Create initial schedules based on the new request.
            $this->createInitialSchedules($subscription);
        }
    }

    /**
     * Soft delete subscription and remove schedule cleanings from database.
     */
    public function remove(Subscription $subscription)
    {
        $subscription->schedules()->future()->forceDelete();
    }

    /**
     * Check if the subscription will collide with other schedules.
     *
     * @param SubscriptionWizardRequestDTO|UpdateSubscriptionRequestDTO:null $request
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
        $endAt = $request ? $request->end_at : ($subscription->end_at ? $subscription->end_at->format('Y-m-d') : null);

        $collidedSchedules = $this->subscriptionService->checkCollision(
            $request ? $request->cleaning_detail->team_id : $subscription->subscribable->team_id,
            $request ? $request->frequency : $subscription->frequency,
            $request ? $request->start_at : $subscription->start_at->format('Y-m-d'),
            $request ? $request->cleaning_detail->start_time : $subscription->subscribable->start_time,
            $endAt,
            $request ? $request->cleaning_detail->end_time : $subscription->subscribable->end_time,
            $request ? null : $subscription->id,
            $timezone,
        );

        if ($collidedSchedules->isNotEmpty()) {
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
     * Fill value end at and end time at.
     *
     * @param  SubscriptionWizardRequestDTO|UpdateSubscriptionRequestDTO  $request
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

    /**
     * Check if the subscription's schedules should be continue.
     * If the subscription's schedules should be continue,
     * the new request just has different frequency.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     * @return bool
     */
    private function shouldContinueSchedules($request, $subscription)
    {
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;

        return $detail->team_id === $request->cleaning_detail->team_id &&
            $subscription->start_at->format('Y-m-d') === $request->start_at &&
            $subscription->end_at?->format('Y-m-d') === $request->end_at &&
            $detail->start_time === $request->cleaning_detail->start_time &&
            $detail->end_time === $request->cleaning_detail->end_time;
    }
}
