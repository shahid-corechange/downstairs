<?php

namespace Database\Factories;

use App\Enums\Credit\CreditTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credit>
 */
class CreditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(0, 12);

        return [
            'valid_until' => now('UTC')->addYear(),
            'initial_amount' => $amount,
            'remaining_amount' => $amount,
            'type' => CreditTypeEnum::Granted(),
            'description' => 'Credit granted to user',
        ];
    }

    public function forUser(int $userId): self
    {
        return $this->state([
            'user_id' => $userId,
        ]);
    }

    public function setType(string $type): self
    {
        return $this->state([
            'type' => $type,
        ]);
    }

    public function setIssuer(int $issuerId): self
    {
        return $this->state([
            'issuer_id' => $issuerId,
        ]);
    }
}
