<?php

namespace App\Services\Subscription;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Models\FixedPrice;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionService
{
    /**
     * Get fixed price for create a subscription.
     */
    public function getFixedPrice(int|float $price, int $vat, int $userId, bool $hasRut): FixedPrice
    {
        /**
         * Get fixed price where fixed price row type is service
         * and price is equal to new price
         */
        $fixedPrice = FixedPrice::withTrashed()
            ->where('user_id', $userId)
            ->where('is_per_order', true)
            ->whereHas('rows', function ($query) use ($price, $hasRut) {
                $query->where('type', FixedPriceRowTypeEnum::Service())
                    ->where('price', $price)
                    ->where('has_rut', $hasRut);
            })
            ->first();

        // If fixed price not found, create new fixed price
        if (! $fixedPrice) {
            $fixedPrice = FixedPrice::create([
                'user_id' => $userId,
                'is_per_order' => true,
            ]);

            $fixedPrice->rows()->create([
                'type' => FixedPriceRowTypeEnum::Service(),
                'quantity' => 1,
                'price' => $price,
                'vat_group' => $vat,
                'has_rut' => $hasRut,
            ]);
        }

        return $fixedPrice;
    }

    /**
     * Check if the subscription will collide with other schedules.
     *
     * @param  int  $teamId
     * @param  int  $frequency
     * @param  string  $startAt
     * @param  string  $startTimeAt
     * @param  string|null  $endAt
     * @param  string  $endTimeAt
     * @param  int|null  $excludeId
     * @param  string  $timezone
     * @return \Illuminate\Support\Collection<array-key,\App\Models\Schedule>
     */
    public function checkCollision(
        $teamId,
        $frequency,
        $startAt,
        $startTimeAt,
        $endAt,
        $endTimeAt,
        $excludeId = null,
        $timezone = 'Europe/Stockholm'
    ) {
        $schedules = [];
        // If the frequency is once, the end date will be the same as the start date
        $endAt = $frequency === SubscriptionFrequencyEnum::Once() ? $startAt : $endAt;
        $carbonStartAt = Carbon::create($startAt.$startTimeAt);
        $initialOffset = $carbonStartAt->copy()->setTimezone($timezone)->format('O') / 100;

        if ($endAt) {
            $carbonEndAt = Carbon::create($endAt.$startTimeAt);
        } else {
            // if end at is not specified, we will check a year ahead
            $carbonEndAt = $carbonStartAt->copy()->addDays(max_schedule_days());
        }

        while ($carbonStartAt->lte($carbonEndAt)) {
            $fullStartAt = $carbonStartAt->copy();

            if ($endTimeAt > $startTimeAt) {
                $fullEndAt = $carbonStartAt->copy()->setTimeFromTimeString($endTimeAt);
            } else {
                $fullEndAt = $carbonStartAt->copy()->setTimeFromTimeString($endTimeAt);
                $fullEndAt->addDay();
            }

            $startAtOffset = $fullStartAt->copy()->setTimezone($timezone)->format('O') / 100;
            $endAtOffset = $fullEndAt->copy()->setTimezone($timezone)->format('O') / 100;

            if ($startAtOffset !== $initialOffset) {
                $fullStartAt->addHours($initialOffset - $startAtOffset);
            }

            if ($endAtOffset !== $initialOffset) {
                $fullEndAt->addHours($initialOffset - $endAtOffset);
            }

            $schedules[] = [
                'start_at' => $fullStartAt,
                'end_at' => $fullEndAt,
            ];

            if ($frequency === SubscriptionFrequencyEnum::Once()) {
                break;
            }

            $carbonStartAt->addWeeks($frequency);
        }

        $collidedSchedules = Schedule::where('team_id', $teamId)
            ->where('status', '!=', ScheduleStatusEnum::Cancel())
            ->where('subscription_id', '!=', $excludeId)
            ->where(function (Builder $query) use ($schedules) {
                foreach ($schedules as $schedule) {
                    $query->orWhereNot(function (Builder $query) use ($schedule) {
                        $query->where('start_at', '>=', $schedule['end_at'])
                            ->orWhere('end_at', '<=', $schedule['start_at']);
                    });
                }
            })
            ->get();

        return $collidedSchedules;
    }
}
