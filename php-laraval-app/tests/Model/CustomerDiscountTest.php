<?php

namespace Tests\Model;

use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Models\CustomerDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerDiscountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customerDiscountsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('customer_discounts', [
                'id',
                'user_id',
                'type',
                'value',
                'start_date',
                'end_date',
                'usage_limit',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function canGetCurrentDiscountByUser(): void
    {
        $type = CustomerDiscountTypeEnum::Cleaning();
        $discount = CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'value' => fake()->numberBetween(1, 10) * 10,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 1,
        ]);

        $currentDiscount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            $type
        );

        $this->assertEquals($discount->id, $currentDiscount->id);
    }

    /** @test */
    public function canGetCurrentIndefinitelyDiscount(): void
    {
        $type = CustomerDiscountTypeEnum::Cleaning();
        $discount = CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'value' => fake()->numberBetween(1, 10) * 10,
            'start_date' => null,
            'end_date' => null,
            'usage_limit' => 1,
        ]);

        $currentDiscount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            $type
        );

        $this->assertEquals($discount->id, $currentDiscount->id);
    }

    /** @test */
    public function canGetCurrentUnlimitedUsageDiscount(): void
    {
        $type = CustomerDiscountTypeEnum::Cleaning();
        $discount = CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'value' => fake()->numberBetween(1, 10) * 10,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'usage_limit' => null,
        ]);

        $currentDiscount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            $type
        );

        $this->assertEquals($discount->id, $currentDiscount->id);
    }

    /** @test */
    public function canGetCurrentUnlimitedUsageAndIndefinitelyDiscount(): void
    {
        $type = CustomerDiscountTypeEnum::Cleaning();
        $discount = CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'value' => fake()->numberBetween(1, 10) * 10,
            'start_date' => null,
            'end_date' => null,
            'usage_limit' => null,
        ]);

        $currentDiscount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            $type
        );

        $this->assertEquals($discount->id, $currentDiscount->id);
    }

    /** @test */
    public function canUseDiscount(): void
    {
        $type = CustomerDiscountTypeEnum::Cleaning();
        CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'value' => fake()->numberBetween(1, 10) * 10,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 2,
        ]);

        CustomerDiscount::useDiscount(
            $this->user->id,
            $type
        );

        $currentDiscount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            $type
        );

        $this->assertEquals(1, $currentDiscount->usage_limit);
    }

    /** @test */
    public function canUseUnlimitedUsageDiscount(): void
    {
        $type = CustomerDiscountTypeEnum::Cleaning();
        CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'value' => fake()->numberBetween(1, 10) * 10,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'usage_limit' => null,
        ]);

        CustomerDiscount::useDiscount(
            $this->user->id,
            $type
        );

        $currentDiscount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            $type
        );

        $this->assertEquals(null, $currentDiscount->usage_limit);
    }
}
