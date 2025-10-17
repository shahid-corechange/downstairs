<?php

namespace Tests\Model;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Models\OrderFixedPrice;
use App\Models\OrderFixedPriceRow;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderFixedPriceTest extends TestCase
{
    /** @test */
    public function orderFixedPricesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('order_fixed_prices', [
                'id',
                'is_per_order',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function orderFixedPriceHasRows(): void
    {
        $orderFixedPrice = OrderFixedPrice::create();
        $orderFixedPrice->rows()->create([
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => (fake()->numberBetween(1, 10) * 100) / (1 + 25 / 100),
            'vat_group' => 25,
            'has_rut' => true,
        ]);

        $this->assertIsObject($orderFixedPrice->rows);
        $this->assertInstanceOf(OrderFixedPriceRow::class, $orderFixedPrice->rows->first());
    }
}
