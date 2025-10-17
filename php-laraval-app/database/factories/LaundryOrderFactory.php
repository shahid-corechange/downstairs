<?php

namespace Database\Factories;

use App\Enums\Contact\ContactTypeEnum;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Models\LaundryPreference;
use App\Models\Store;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaundryOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        /** @var User $user */
        $user = User::role('Customer')->inRandomOrder()->first();
        /** @var User $causer */
        $causer = User::role('Superadmin')->inRandomOrder()->first();
        $statuses = [
            LaundryOrderStatusEnum::Pending(),
            LaundryOrderStatusEnum::Paid(),
            LaundryOrderStatusEnum::Done(),
        ];
        $orderedAt = now()->addDays(rand(1, 30));

        return [
            'store_id' => Store::inRandomOrder()->first()->id,
            'user_id' => $user->id,
            'causer_id' => $causer->id,
            'customer_id' => $user->customers()->where('type', ContactTypeEnum::Primary())->first()->id,
            'laundry_preference_id' => LaundryPreference::inRandomOrder()->first()->id,
            'status' => fake()->randomElement($statuses),
            'pickup_property_id' => $user->properties()->inRandomOrder()->first()->id,
            'pickup_team_id' => Team::inRandomOrder()->first()->id,
            'pickup_time' => $orderedAt->addHours(rand(1, 12)),
            'delivery_property_id' => $user->properties()->inRandomOrder()->first()->id,
            'delivery_team_id' => Team::inRandomOrder()->first()->id,
            'delivery_time' => $orderedAt->addDays(2)->addHours(rand(1, 12)),
            'ordered_at' => $orderedAt,
        ];
    }

    public function forStore(int $storeId): self
    {
        return $this->state([
            'store_id' => $storeId,
        ]);
    }
}
