<?php

namespace App\Services\Subscription;

use App\DTOs\Subscription\SubscriptionLaundryOrderDTO;
use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Jobs\CreateLaundryDeliveryScheduleJob;
use App\Jobs\CreateLaundryOrderJob;
use App\Models\BlockDay;
use App\Models\Schedule;
use App\Models\Subscription;
use App\Models\SubscriptionLaundryDetail;
use Carbon\Carbon;

class SubscriptionLaundryScheduleService
{
    /**
     * Create the initial schedules for a subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $scheduleNotQueue
     */
    public function createInitialSchedules($subscription, $scheduleNotQueue = 5)
    {
        $initialStartPickupTime = $this->getInitialStartPickupTime($subscription);
        $initialEndPickupTime = $this->getEndPickupTime($initialStartPickupTime);
        $refillSchedules = $this->getRefillSequence($subscription, 0);

        $orders = $this->buildLaundryOrders(
            $subscription,
            $initialStartPickupTime,
            $initialEndPickupTime,
            $refillSchedules
        );

        $this->dispatchLaundryOrders($orders, $scheduleNotQueue);
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
            ->whereHas(
                'scheduleable',
                function ($query) {
                    $query->where('type', ScheduleLaundryTypeEnum::Pickup());
                }
            )
            ->orderBy('original_start_at', 'desc');

        $latestSchedules = $currentSchedules->first();

        [$start_at, $end_at] = $this->getDateTimes($subscription, $latestSchedules);
        $refillSchedules = $this->getRefillSequence(
            $subscription,
            $currentSchedules->future()->count()
        );

        $orders = $this->buildLaundryOrders(
            $subscription,
            $start_at,
            $end_at,
            $refillSchedules
        );

        $this->dispatchLaundryOrders($orders, $scheduleNotQueue);
    }

    private function dispatchLaundryOrders($orders, $scheduleNotQueue = 5)
    {
        foreach ($orders as $index => $order) {
            if ($index < $scheduleNotQueue) {
                CreateLaundryOrderJob::dispatchAfterResponse($order);
            } else {
                CreateLaundryOrderJob::dispatch($order);
            }
        }
    }

    /**
     * Dispatch the delivery schedules
     *
     * @param  \Illuminate\Database\Eloquent\Collection<string|int, \App\Models\LaundryOrder>  $orders
     * @param  int  $scheduleNotQueue
     */
    public function dispatchDeliverySchedules($orders, $scheduleNotQueue = 5)
    {
        $days = max_schedule_days();
        $blockDays = BlockDay::whereBetween('block_date', [now(), now()->addDays($days)])
            ->pluck('block_date');

        foreach ($orders as $index => $order) {
            $pickupSchedule = $order->schedules()
                ->where('type', ScheduleLaundryTypeEnum::Pickup())
                ->first();

            // Compose delivery schedule based on pickup schedule
            $deliverySchedule = $this->composeDeliverySchedule(
                $order->subscription,
                $pickupSchedule->original_start_at,
                $pickupSchedule->note,
                $blockDays
            );

            if ($index < $scheduleNotQueue) {
                CreateLaundryDeliveryScheduleJob::dispatchAfterResponse($order, $deliverySchedule);
            } else {
                CreateLaundryDeliveryScheduleJob::dispatch($order, $deliverySchedule);
            }
        }
    }

    /**
     * Get the initial start and end dates of the subscription.
     *
     * @param  Subscription  $subscription
     * @param  ScheduleLaundry|null  $latestSchedules
     */
    public function getDateTimes($subscription, $latestSchedules = null): array
    {
        $startAt = $this->getStartPickupTime($subscription, $latestSchedules);
        $endAt = $this->getEndPickupTime($startAt);

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

    private function getInitialStartPickupTime(Subscription $subscription)
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;

        /** @var \Carbon\Carbon $start_at */
        $start_at = $subscription->start_at
            ->copy()
            ->setTimeFromTimeString($detail->pickup_time);

        // If start day is before today
        if ($start_at < now()) {
            $start_at = Carbon::now()->startOfWeek()
                ->next($start_at->dayOfWeek)
                ->setTimeFromTimeString($detail->pickup_time);
        }

        return $start_at;
    }

    /**
     * To get the start time of the pickup schedule
     *
     * @param  Subscription  $subscription
     * @param  ScheduleLaundry  $latestSchedules
     */
    private function getStartPickupTime($subscription, $latestSchedules): Carbon
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;

        /** @var \Carbon\Carbon $start_at */
        $start_at = Carbon::create($latestSchedules->original_start_at)
            ->addWeeks($subscription->frequency)
            ->setTimeFromTimeString($detail->pickup_time);

        // If start day is before today
        if ($start_at < now()) {
            $start_at = Carbon::now()->startOfWeek()
                ->next($start_at->dayOfWeek)
                ->setTimeFromTimeString($detail->pickup_time);
        }

        return $start_at;
    }

    /**
     * To get the end pickup time
     *
     * @param  Carbon  $start_at
     */
    private function getEndPickupTime($start_at): Carbon
    {
        // add 1 quarter or 15 minutes to the start pickup time
        return $start_at->copy()->addMinutes(15);
    }

    /**
     * To build the laundry orders
     *
     * @param  Subscription  $subscription
     * @param  Carbon  $start_at
     * @param  Carbon  $end_at
     * @param  int  $refillSchedules
     * @return SubscriptionLaundryOrderDTO[]
     */
    private function buildLaundryOrders(
        $subscription,
        $start_at,
        $end_at,
        $refillSchedules,
    ) {
        $data = [];
        $days = max_schedule_days();
        $blockDays = BlockDay::whereBetween('block_date', [now(), now()->addDays($days)])
            ->pluck('block_date');

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

            $data[] = $this->composeLaundryOrder(
                $subscription,
                $start_at,
                $end_at,
                $blockDays
            );

            // Jump to next week
            $start_at->addWeeks($subscription->frequency);
            $end_at->addWeeks($subscription->frequency);
        }

        return $data;
    }

    /**
     * Compose the laundry order
     *
     * @param  Subscription  $subscription
     * @param  Carbon  $startAt
     * @param  Carbon  $endAt
     * @param  \Illuminate\Support\Collection  $blockDays
     */
    private function composeLaundryOrder(
        $subscription,
        $startAt,
        $endAt,
        $blockDays,
    ) {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;
        $scheduleNote = array_filter([
            'subscription_note' => $subscription->description,
            'property_note' => $detail->pickupProperty->getMeta('note'),
        ]);

        $pickupSchedule = SubscriptionScheduleDTO::from([
            'user_id' => $subscription->user_id,
            'service_id' => $subscription->service_id,
            'team_id' => $detail->pickup_team_id,
            'customer_id' => $subscription->customer_id,
            'property_id' => $detail->pickup_property_id,
            'status' => ScheduleStatusEnum::Booked(),
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
            'quarters' => 1,
            'key_information' => $subscription->property->key_description,
            'note' => empty($scheduleNote) ? ['note' => ''] : $scheduleNote,
            'is_fixed' => $subscription->is_fixed,
        ]);

        $deliverySchedule = null;

        if ($detail->delivery_team_id) {
            $deliverySchedule = $this->composeDeliverySchedule(
                $subscription,
                $startAt,
                $blockDays
            );
        }

        return SubscriptionLaundryOrderDTO::from([
            'subscription' => $subscription,
            'pickup_schedule' => $pickupSchedule,
            'delivery_schedule' => $deliverySchedule,
        ]);
    }

    /**
     * Compose the delivery schedule
     *
     * @param  Subscription  $subscription
     * @param  Carbon  $startAt
     * @param  \Illuminate\Support\Collection  $blockDays
     * @return SubscriptionScheduleDTO
     */
    private function composeDeliverySchedule($subscription, $startAt, $blockDays)
    {
        /** @var \App\Models\SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;
        $deliveryTime = $this->getDeliveryTime($detail, $startAt, $blockDays);
        $scheduleNote = array_filter([
            'subscription_note' => $subscription->description,
            'property_note' => $detail->deliveryProperty->getMeta('note'),
        ]);

        return SubscriptionScheduleDTO::from([
            'user_id' => $subscription->user_id,
            'service_id' => $subscription->service_id,
            'team_id' => $detail->delivery_team_id,
            'customer_id' => $subscription->customer_id,
            'property_id' => $detail->delivery_property_id,
            'status' => ScheduleStatusEnum::Booked(),
            'start_at' => $deliveryTime->toDateTimeString(),
            'end_at' => $deliveryTime->copy()->addMinutes(15)->toDateTimeString(),
            'quarters' => 1,
            'key_information' => $subscription->property->key_description,
            'note' => empty($scheduleNote) ? ['note' => ''] : $scheduleNote,
            'is_fixed' => $subscription->is_fixed,
        ]);
    }

    /**
     * Get the delivery time
     *
     * @param  SubscriptionLaundryDetail  $detail
     * @param  Carbon  $startAt
     * @param  \Illuminate\Support\Collection  $blockDays
     */
    private function getDeliveryTime($detail, $startAt, $blockDays)
    {
        // Add hours to the start pickup time based on the laundry preference.
        $deliveryTime = $startAt->copy()
            ->addHours($detail->preference->hours)
            ->setTimeFromTimeString($detail->delivery_time);

        // Jump to next day if the day is blocked
        while (isset($blockDays) && $blockDays->search($deliveryTime->format('Y-m-d'))) {
            $deliveryTime->addDay();
        }

        return $deliveryTime;
    }
}
