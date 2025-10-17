<?php

namespace App\Services;

use App\Models\CustomerDiscount;
use Carbon\Carbon;

class DiscountService
{
    /**
     * Check if the discount is active.
     */
    public static function isActive(?Carbon $startDate, ?Carbon $endDate, ?int $usageLimit, ?Carbon $deletedAt): bool
    {
        // If the discount has been deleted or the usage limit is 0, it is not active.
        if (! is_null($deletedAt) || $usageLimit === 0) {
            return false;
        }

        // If the discount has start date but it is in the future, it is not active.
        if (! is_null($startDate) && $startDate->isFuture()) {
            return false;
        }

        // If the discount has end date but it is in the past, it is not active.
        if (! is_null($endDate) && $endDate->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Use the discount.
     */
    public static function useDiscount(?CustomerDiscount $discount): void
    {
        if ($discount && $discount->usage_limit !== null) {
            $discount->update([
                'usage_limit' => $discount->usage_limit - 1,
            ]);
        }
    }
}
