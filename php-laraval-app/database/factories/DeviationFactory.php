<?php

namespace Database\Factories;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Models\Deviation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adress>
 */
class DeviationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deviation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'type' => DeviationTypeEnum::values()[
                $this->faker->unique(true)->numberBetween(0, count(DeviationTypeEnum::values()) - 1)
            ],
            'reason' => $this->faker->paragraph,
            'is_handled' => false,
        ];
    }

    public function forType(string $type): static
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'type' => $type,
            ];
        });
    }
}
