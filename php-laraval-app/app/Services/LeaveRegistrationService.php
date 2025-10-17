<?php

namespace App\Services;

use App\Models\LeaveRegistration;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRegistrationService
{
    /**
     * Generate leave registration details base on leave registration.
     */
    public static function generateDetails(LeaveRegistration $leaveRegistration): array
    {
        $details = self::generateDetailsFromDates(
            $leaveRegistration->start_at,
            $leaveRegistration->end_at,
            $leaveRegistration->details->max('start_at'),
        );

        return $details;
    }

    /**
     * Generate leave registrations details from the given start and end date.
     */
    public static function generateDetailsFromDates(
        Carbon $startAt,
        ?Carbon $endAt,
        Carbon $lastCreatedStartAt = null,
        bool $forceStop = false
    ): array {
        $endOfLastMonth = now('Europe/Stockholm')->subMonth()->endOfMonth();
        $stockholmStartAt = $startAt->copy()->setTimezone('Europe/Stockholm');

        if ($stockholmStartAt->hour >= 16) {
            // If the start period is after normal working hours, set it to the next day
            $stockholmStartAt->addDay()->startOfDay();
        }

        /** @var Carbon */
        $startPeriod = $lastCreatedStartAt
            ? $lastCreatedStartAt->copy()->setTimezone('Europe/Stockholm')->addDay()->startOfDay()
            : $stockholmStartAt;

        /** @var Carbon */
        $endPeriod = $endOfLastMonth;

        if (! is_null($endAt)) {
            $stockholmEndAt = $endAt->copy()->setTimezone('Europe/Stockholm');

            /** @var Carbon */
            $endPeriod = $forceStop ? $stockholmEndAt : min($stockholmEndAt, $endOfLastMonth);

            if ($endPeriod->hour < 8) {
                // If the end period is before normal working hours, set it to the previous day
                $endPeriod->subDay()->endOfDay();
            }
        }

        $details = array_map(
            function ($date) use ($startPeriod, $endPeriod) {
                $normalStartAt = $date->copy()->setTime(8, 0);
                $normalEndAt = $date->copy()->setTime(16, 0);

                return [
                    'start_at' => max($startPeriod, $normalStartAt)->utc(),
                    'end_at' => min($endPeriod, $normalEndAt)->utc(),
                ];
            },
            // Set end period to end of day to include the last day
            CarbonPeriod::create($startPeriod, $endPeriod->copy()->endOfDay())->toArray()
        );

        return $details;
    }

    /**
     * Check if the given leave registration should be stopped.
     */
    public static function shouldStop(?Carbon $endAt, Carbon $lastCreatedStartAt): bool
    {
        if (is_null($endAt)) {
            return false;
        }

        $stockholmEndAt = $endAt->copy()->setTimezone('Europe/Stockholm');
        $stockholmLastCreatedStartAt = $lastCreatedStartAt->copy()->setTimezone('Europe/Stockholm');

        if ($stockholmEndAt->hour < 8) {
            // If the end period is before normal working hours, set it to the previous day
            $stockholmEndAt->subDay()->endOfDay();
        }

        return $stockholmEndAt->isSameDay($stockholmLastCreatedStartAt);
    }
}
