<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adress>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = fake();

        return [
            'city_id' => config('downstairs.test.city_id'),
            'address' => $faker->streetAddress,
            'address_2' => null,
            'area' => fake()->randomElement($this->getAreas()),
            'postal_code' => Str::replace(' ', '', $faker->postcode),
            'latitude' => null,
            'longitude' => null,
        ];
    }

    private function getAreas()
    {
        return [
            'Västra Frölunda',
            'Hisings Backa',
            'Angered',
            'Billdal',
        ];
    }
}
