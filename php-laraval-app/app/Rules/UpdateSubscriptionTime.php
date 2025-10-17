<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateSubscriptionTime implements ValidationRule
{
    public function __construct(
        private string $attribute,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $date = request('startAt');
        $datetime = $date ? Carbon::create($date.$value) :
        now()->setTimeFromTimeString($value);

        /** @var Subscription $subscription */
        $subscription = request()->route('subscription');
        $originalDateTime = Carbon::create($subscription->start_at->format('Y-m-d').' '.
            $subscription->subscribable[$this->attribute]);

        if ($originalDateTime->toISOString() === $datetime->toISOString()) {
            return;
        }

        if ($datetime->isPast()) {
            $fail(__('time after now validation message'));
        }
    }
}
