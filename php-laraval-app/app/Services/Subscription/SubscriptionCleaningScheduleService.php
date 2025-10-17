<?php

namespace App\Services\Subscription;

use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Jobs\CreateScheduleCleaningJob;
use App\Models\BlockDay;
use App\Models\Schedule;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionCleaningScheduleService
{
    /**
     * Create the initial schedules for a subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $scheduleNotQueue
     */
    public function createInitialSchedules($subscription, $scheduleNotQueue = 5)
    {
        $initialStartDate = $this->getInitialStartDate($subscription);
        $initialEndDate = $this->getEndDate($subscription, $initialStartDate);
        $refillSchedules = $this->getRefillSequence($subscription, 0);

        $schedules = $this->buildSchedules(
            $subscription,
            $initialStartDate,
            $initialEndDate,
            $refillSchedules
        );

        $this->dispatchSchedules($schedules, $scheduleNotQueue);
    }

    /**
     * Create the next schedules for a subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $scheduleNotQueue
     */
    public function createNextSchedules($subscription, $scheduleNotQueue = 5)
    {
        /** @var \Illuminate\Database\Query\Builder */
        $currentSchedules = Schedule::where('subscription_id', $subscription->id)
            ->orderBy('original_start_at', 'desc');

        $latestSchedules = $currentSchedules->first();

        [$start_at, $end_at] = $this->getDateTimes($subscription, $latestSchedules);
        $refillSchedules = $this->getRefillSequence(
            $subscription,
            $currentSchedules->future()->count()
        );

        $schedules = $this->buildSchedules(
            $subscription,
            $start_at,
            $end_at,
            $refillSchedules
        );

        $this->dispatchSchedules($schedules, $scheduleNotQueue);
    }

    private function dispatchSchedules($schedules, $scheduleNotQueue = 5)
    {
        foreach ($schedules as $index => $schedule) {
            if ($index < $scheduleNotQueue) {
                CreateScheduleCleaningJob::dispatchAfterResponse($schedule);
            } else {
                CreateScheduleCleaningJob::dispatch($schedule);
            }
        }
    }

    /**
     * Get the initial start and end dates of the subscription.
     *
     * @param  Subscription  $subscription
     * @param  Schedule|null  $latestSchedules
     */
    public function getDateTimes($subscription, $latestSchedules = null): array
    {
        $startAt = $this->getStartDate($subscription, $latestSchedules);
        $endAt = $this->getEndDate($subscription, $startAt);

        return [$startAt, $endAt];
    }

    /**
     * Get the refill sequence of the subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $totalFutureSchedules
     */
    public function getRefillSequence($subscription, $totalFutureSchedules): int
    {
        if ($subscription->frequency !== SubscriptionFrequencyEnum::Once()) {
            $refillSequence = get_setting(
                GlobalSettingEnum::SubscriptionRefillSequence(),
                config('downstairs.subscription.refillSequence')
            );
            $refill = floor($refillSequence / $subscription->frequency);

            return $refill > $totalFutureSchedules ? $refill - $totalFutureSchedules : 0;
        } else {
            return $totalFutureSchedules === 0 ? 1 : 0;
        }
    }

    public function getRefillSequenceByFrequency(int $frequency)
    {
        return $frequency === SubscriptionFrequencyEnum::Once()
            ? 1
            : get_setting(
                GlobalSettingEnum::SubscriptionRefillSequence(),
                config('downstairs.subscription.refillSequence')
            );
    }

    private function getInitialStartDate(Subscription $subscription)
    {
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;

        /** @var \Carbon\Carbon $start_at */
        $start_at = $subscription->start_at
            ->copy()
            ->setTimeFromTimeString($detail->start_time);

        // If start day is before today
        if ($start_at < now()) {
            $start_at = Carbon::now()->startOfWeek()
                ->next($start_at->dayOfWeek)
                ->setTimeFromTimeString($detail->start_time);
        }

        return $start_at;
    }

    /**
     * To get the start date of the subscription
     *
     * @param  Subscription  $subscription
     * @param  Schedule  $latestSchedules
     */
    private function getStartDate($subscription, $latestSchedules): Carbon
    {
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;

        /** @var \Carbon\Carbon $start_at */
        $start_at = Carbon::create($latestSchedules->original_start_at)
            ->addWeeks($subscription->frequency)
            ->setTimeFromTimeString($detail->start_time);

        // If start day is before today
        if ($start_at < now()) {
            $start_at = Carbon::now()->startOfWeek()
                ->next($start_at->dayOfWeek)
                ->setTimeFromTimeString($detail->start_time);
        }

        return $start_at;
    }

    /**
     * To get the end date of the subscription
     *
     * @param  Subscription  $subscription
     * @param  Carbon  $start_at
     */
    private function getEndDate($subscription, $start_at): Carbon
    {
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;
        $end_at = Carbon::create($start_at);
        $end_at->setTimeFromTimeString($detail->end_time);

        // If end day is before start day
        if ($end_at->lt($start_at)) {
            $end_at->addDay();
        }

        return $end_at;
    }

    /**
     * To build the schedules
     *
     * @param  Subscription  $subscription
     * @param  Carbon  $start_at
     * @param  Carbon  $end_at
     * @param  int  $refillSchedules
     * @return SubscriptionScheduleDTO[]
     */
    private function buildSchedules(
        $subscription,
        $start_at,
        $end_at,
        $refillSchedules,
    ) {
        $data = [];
        $days = max_schedule_days();
        $blockDays = BlockDay::whereBetween('block_date', [now(), now()->addDays($days)])
            ->pluck('block_date');
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;
        $scheduleNote = array_filter([
            'subscription_note' => $subscription->description,
            'property_note' => $detail->property->getMeta('note'),
        ]);

        for ($x = 1; $x <= $refillSchedules; $x++) {
            // Don't create schedule if end date is before today
            if (isset($subscription->end_at)
                && $subscription->end_at->format('Y-m-d') < $start_at->format('Y-m-d')) {
                break;
            } elseif ($start_at->isAfter(now()->addDays($days)->endOfDay())) {
                break;
            }

            // Jump to next week if the day is blocked
            while (isset($blockDays) && $blockDays->search($start_at->format('Y-m-d'))) {
                $start_at->addWeeks($subscription->frequency);
                $end_at->addWeeks($subscription->frequency);
            }

            $data[] = SubscriptionScheduleDTO::from([
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'service_id' => $subscription->service_id,
                'team_id' => $detail->team_id,
                'customer_id' => $subscription->customer_id,
                'property_id' => $detail->property_id,
                'status' => ScheduleStatusEnum::Booked(),
                'start_at' => $start_at->toDateTimeString(),
                'end_at' => $end_at->toDateTimeString(),
                'quarters' => $detail->quarters,
                'key_information' => $detail->property->key_description,
                'note' => empty($scheduleNote) ? ['note' => ''] : $scheduleNote,
                'is_fixed' => $subscription->is_fixed,
            ]);

            // Jump to next week
            $start_at->addWeeks($subscription->frequency);
            $end_at->addWeeks($subscription->frequency);
        }

        return $data;
    }
}
