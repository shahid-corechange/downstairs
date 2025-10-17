<?php

namespace Database\Factories;

use App\Enums\MembershipTypeEnum;
use App\Enums\Property\PropertyStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake();
        $squareMeter = $faker->randomFloat(2, 0, 100);

        return [
            'membership_type' => fake()->randomElement(MembershipTypeEnum::values()),
            'property_type_id' => 1,
            'square_meter' => $squareMeter,
            'status' => PropertyStatusEnum::Active(),
            'key_information' => [
                'key_place' => null,
                'front_door_code' => $faker->randomElement([$faker->words(3, true), null]),
                'alarm_code_off' => $faker->randomElement([$faker->words(3, true), null]),
                'alarm_code_on' => $faker->randomElement([$faker->words(3, true), null]),
                'information' => $faker->randomElement([$faker->words(3, true), null]),
            ],
        ];
    }

    public function assignAddress(int $addressId)
    {
        return $this->state(function ($attributes) use ($addressId) {
            return [
                'address_id' => $addressId,
            ];
        });
    }

    public function setMembershipType(string $type)
    {
        return $this->state(function ($attributes) use ($type) {
            return [
                'membership_type' => $type,
            ];
        });
    }
}
