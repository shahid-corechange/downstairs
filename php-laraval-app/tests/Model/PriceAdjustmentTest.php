<?php

namespace Tests\Model;

use App\Models\PriceAdjustment;
use App\Models\PriceAdjustmentRow;
use App\Models\User;
use Database\Seeders\PriceAdjustmentSeeder;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PriceAdjustmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PriceAdjustmentSeeder::createPriceAdjustment();
    }

    /** @test */
    public function priceAdjustmentsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('price_adjustments', [
                'id',
                'causer_id',
                'type',
                'description',
                'price_type',
                'price',
                'execution_date',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function priceAdjustmentHasRow(): void
    {
        $priceAdjustment = PriceAdjustment::first();

        $this->assertInstanceOf(PriceAdjustmentRow::class, $priceAdjustment->rows->first());
    }

    /** @test */
    public function priceAdjustmentHasCauser(): void
    {
        $priceAdjustment = PriceAdjustment::first();

        $this->assertInstanceOf(User::class, $priceAdjustment->causer);
    }
}
