<?php

namespace Tests\Model;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Models\OrderFixedPrice;
use App\Models\OrderFixedPriceRow;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderFixedPriceRowTest extends TestCase
{
    /** @test */
    public function orderFixedPriceRowsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('order_fixed_price_rows', [
                'id',
                'order_fixed_price_id',
                'type',
                'description',
                'quantity',
                'price',
                'vat_group',
                'has_rut',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function orderFixedPriceRowHasPriceWithVat(): void
    {
        $orderFixedPrice = OrderFixedPrice::create([]);
        $orderFixedPrice->rows()->create([
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => (fake()->numberBetween(1, 10) * 100) / (1 + 25 / 100),
            'vat_group' => 25,
            'has_rut' => true,
        ]);
        $orderFixedPriceRow = OrderFixedPriceRow::first();

        $this->assertIsFloat($orderFixedPriceRow->price_with_vat);
    }

    /** @test */
    public function orderFixedPriceRowHasOrderFixedPrice(): void
    {
        $orderFixedPrice = OrderFixedPrice::create([]);
        $orderFixedPrice->rows()->create([
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => (fake()->numberBetween(1, 10) * 100) / (1 + 25 / 100),
            'vat_group' => 25,
            'has_rut' => true,
        ]);
        $orderFixedPriceRow = OrderFixedPriceRow::first();

        $this->assertInstanceOf(OrderFixedPrice::class, $orderFixedPriceRow->fixedPrice);
    }
}
