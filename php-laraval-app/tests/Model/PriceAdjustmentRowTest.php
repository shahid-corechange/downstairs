<?php

namespace Tests\Model;

use App\Models\PriceAdjustment;
use App\Models\PriceAdjustmentRow;
use Database\Seeders\PriceAdjustmentSeeder;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PriceAdjustmentRowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PriceAdjustmentSeeder::createPriceAdjustment();
    }

    /** @test */
    public function priceAdjustmentRowsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('price_adjustment_rows', [
                'id',
                'price_adjustment_id',
                'adjustable_type',
                'adjustable_id',
                'previous_price',
                'price',
                'vat_group',
                'status',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function priceAdjustmentRowHasAdjustable(): void
    {
        $priceAdjustmentRow = PriceAdjustmentRow::first();

        $this->assertIsObject($priceAdjustmentRow->adjustable);
    }

    /** @test */
    public function priceAdjustmentRowHasPriceAdjustment(): void
    {
        $priceAdjustmentRow = PriceAdjustmentRow::first();

        $this->assertInstanceOf(PriceAdjustment::class, $priceAdjustmentRow->priceAdjustment);
    }
}
