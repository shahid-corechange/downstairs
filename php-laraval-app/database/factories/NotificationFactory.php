<?php

namespace Database\Factories;

use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
        ];
    }

    public function forUser(User $user): Factory
    {
        if ($user->can(PermissionsEnum::AccessCustomerApp())) {
            return $this->state(function (array $attributes) use ($user) {
                $total = $this->faker->numberBetween(1, 12);

                return [
                    'user_id' => $user->id,
                    'type' => NotificationTypeEnum::CreditRefund(),
                    'hub' => NotificationHubEnum::Customer(),
                    'title' => 'Credit Refund',
                    'description' => 'You have been refunded '.$total.' credits.',

                ];
            });
        } else {
            return $this->state(function (array $attributes) use ($user) {
                $date = $this->faker->dateTimeBetween('now', '+4 months')->format('F, jS Y H:i:s');

                return [
                    'user_id' => $user->id,
                    'type' => NotificationTypeEnum::ScheduleUpdated(),
                    'hub' => NotificationHubEnum::Employee(),
                    'title' => 'Schedule Updated',
                    'description' => "Your schedule at {$date} has been updated.",
                ];
            });
        }
    }
}
