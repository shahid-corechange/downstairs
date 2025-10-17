<?php

namespace App\Rules;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStart implements ValidationRule
{
    public function __construct(
        public ?string $startTime = null,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $startAt = $this->startTime ? Carbon::parse($value)->setTimeFromTimeString($this->startTime) :
            Carbon::parse($value);
        /** @var int $refillSequence */
        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $days = weeks_to_days($refillSequence);
        $refillSequences = SubscriptionRefillSequenceEnum::options();
        $time = array_search($refillSequence, $refillSequences);

        if ($startAt->isAfter(now()->addDays($days)->endOfDay())) {
            $fail('start date cannot be more than a certain time')
                ->translate([
                    'time' => __($time),
                ]);
        }
    }
}
