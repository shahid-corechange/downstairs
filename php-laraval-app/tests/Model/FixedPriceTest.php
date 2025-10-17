<?php

namespace Tests\Model;

use App\Models\FixedPrice;
use App\Models\FixedPriceRow;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FixedPriceTest extends TestCase
{
    /** @test */
    public function fixedPricesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('fixed_prices', [
                'id',
                'user_id',
                'is_per_order',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function fixedPriceHasUser(): void
    {
        $fixedPrice = FixedPrice::first();

        if ($fixedPrice) {
            $this->assertInstanceOf(User::class, $fixedPrice->user);
        } else {
            $this->assertNull($fixedPrice);
        }
    }

    /** @test */
    public function fixedPriceHasSubscriptions(): void
    {
        $fixedPrice = FixedPrice::first();

        if ($fixedPrice) {
            $this->assertIsObject($fixedPrice->subscriptions);
            $this->assertInstanceOf(Subscription::class, $fixedPrice->subscriptions->first());
        } else {
            $this->assertNull($fixedPrice);
        }
    }

    /** @test */
    public function fixedPriceHasRows(): void
    {
        $fixedPrice = FixedPrice::first();

        if ($fixedPrice) {
            $this->assertIsObject($fixedPrice->rows);
            $this->assertInstanceOf(FixedPriceRow::class, $fixedPrice->rows->first());
        } else {
            $this->assertNull($fixedPrice);
        }
    }
}
