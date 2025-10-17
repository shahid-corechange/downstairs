<?php

namespace App\Services;

use App\Models\LaundryPreference;
use Carbon\Carbon;

class LaundryPreferenceService
{
    /**
     * Get the laundry preference from the difference hours between
     * the pickup start at and delivery start at.
     *
     * @param  Carbon|string  $pickupStartAt
     * @param  Carbon|string  $deliveryStartAt
     * @return LaundryPreference
     */
    public static function getPreference($pickupStartAt, $deliveryStartAt)
    {
        $pickupStartAt = Carbon::parse($pickupStartAt);
        $deliveryStartAt = Carbon::parse($deliveryStartAt);

        // Find the difference hours between the pickup start at and delivery start at
        $difference = $pickupStartAt->diffInHours($deliveryStartAt);

        // Get the laundry preference
        return LaundryPreference::where('hours', '<=', $difference)
            ->orderBy('hours', 'desc')
            ->first();
    }
}
