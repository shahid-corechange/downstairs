<?php

namespace Tests\Model;

use App\Models\FixedPrice;
use App\Models\FixedPriceRow;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FixedPriceRowTest extends TestCase
{
    /** @test */
    public function fixedPriceRowsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('fixed_price_rows', [
                'id',
                'fixed_price_id',
                'type',
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
    public function fixedPriceRowHasPriceWithVat(): void
    {
        $row = FixedPriceRow::first();

        if ($row) {
            $this->assertIsFloat($row->price_with_vat);
        } else {
            $this->assertNull($row);
        }
    }

    /** @test */
    public function fixedPriceRowFixedPrice(): void
    {
        $row = FixedPriceRow::first();

        if ($row) {
            $this->assertInstanceOf(FixedPrice::class, $row->fixedPrice);
        } else {
            $this->assertNull($row);
        }
    }
}
