<?php

namespace App\Services;

use App\Models\FixedPrice;
use Carbon\Carbon;

class FixedPriceService
{
    /**
     * Check if the fixed price is applicable on the given date.
     *
     * @param  Carbon|string  $date
     */
    public static function isApplicable(FixedPrice $fixedPrice, $date)
    {
        if ($fixedPrice->deleted_at) {
            return false;
        }

        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        if ($fixedPrice->is_per_order &&
            $fixedPrice->start_date &&
            $carbonDate->lt($fixedPrice->start_date)
        ) {
            return false;
        }

        if ($fixedPrice->is_per_order &&
            $fixedPrice->end_date &&
            $carbonDate->gt($fixedPrice->end_date)
        ) {
            return false;
        }

        if (! $fixedPrice->is_per_order &&
            $fixedPrice->start_date &&
            $carbonDate->lt($fixedPrice->start_date->startOfMonth())
        ) {
            return false;
        }

        if (! $fixedPrice->is_per_order &&
            $fixedPrice->end_date &&
            $carbonDate->gt($fixedPrice->end_date->endOfMonth())
        ) {
            return false;
        }

        return true;
    }
}
