<?php

namespace Database\Factories;

use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected const QUARTERS = 4;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $start_hour = fake()->numberBetween(6, 18); // Generate hours between 6 AM and 6 PM
        $start_time_at = Carbon::createFromTime($start_hour, 0, 0)->format('H:i:s');
        $timeToAdd = fake()->numberBetween(1, 6);
        $end_hour = ($start_hour + $timeToAdd) % 24;
        $end_time_at = Carbon::createFromTime($end_hour, 0, 0)->format('H:i:s');
        $frequency = fake()->randomElement([1, 2, 4]);
        $quarters = $timeToAdd * self::QUARTERS;

        $start_at = fake()->dateTimeBetween('-7 days', '+1 month')->format('Y-m-d');

        return [
            'frequency' => $frequency,
            'start_at' => $start_at,
            'start_time_at' => $start_time_at,
            'end_time_at' => $end_time_at,
            'quarters' => $quarters,
            'refill_sequence' => SubscriptionRefillSequenceEnum::OneYear(),
            'is_paused' => 0,
            'is_fixed' => 0,
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state([
            'user_id' => $user->id,
            'customer_id' => $user->customers->random()->id,
            'property_id' => $user->properties->random()->id,
            'service_id' => str_starts_with($user->email, 'company') ? 3 : 1,
        ]);
    }
}
