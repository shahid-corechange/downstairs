<?php

namespace Database\Factories;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\BlockDay;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ScheduleCleaningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = $this->getStartDate();
        $time = $this->faker->numberBetween(1, 5);
        $minute = $this->faker->randomElement([0, 15, 30, 45]);
        $endAt = Carbon::instance($startAt)->addHours($time);
        $quarters = $endAt->diffInMinutes($startAt) / 15;

        return [
            'key_information' => $this->faker->randomElement($this->getKeyInformations()),
            'note' => $this->faker->randomElement($this->getNotes()),
            'is_fixed' => $this->faker->boolean(50),
            'status' => $this->faker->randomElement(ScheduleCleaningStatusEnum::values()),
            'start_at' => $startAt->format("Y-m-d H:{$minute}:00"),
            'end_at' => $endAt->format("Y-m-d H:{$minute}:00"),
            'original_start_at' => $startAt->format("Y-m-d H:{$minute}:00"),
            'quarters' => $quarters,
        ];
    }

    public function getStartDate()
    {
        $blockDays = BlockDay::all();
        $startAt = $this->faker->dateTimeBetween('now', '+1 month');
        $startDate = Carbon::instance($startAt)->format('Y-m-d');

        while ($blockDays->contains('block_date', $startDate)) {
            $startAt = $this->faker->dateTimeBetween('now', '+1 month');
            $startDate = Carbon::instance($startAt)->format('Y-m-d');
        }

        return $startAt;
    }

    /**
     * Set the belongs to relationships state.
     */
    public function forSubscription(Subscription $subscription): static
    {
        return $this->state(function (array $attributes) use ($subscription) {
            return [
                'subscription_id' => $subscription->id,
                'team_id' => $subscription->team_id,
                'customer_id' => $subscription->customer->id,
                'property_id' => $subscription->property->id,
            ];
        });
    }

    public function forStatus(string $status): static
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
            ];
        });
    }

    public function getKeyInformations(): array
    {
        return [
            'Under the door mat',
            'Behind a potted plant near the front entrance',
            'Beneath the garden gnome statue',
        ];
    }

    public function getNotes(): array
    {
        return [
            ['note' => 'Please remember to securely close the front door and ensure that the latch is engaged'],
            ['note' => "Don't let the dogs out"],
            ['note' => 'Be mindful of the curious dog in our family'],
        ];
    }
}
