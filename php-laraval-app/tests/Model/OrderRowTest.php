<?php

namespace Tests\Model;

use App\Models\Order;
use App\Models\OrderRow;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderRowTest extends TestCase
{
    /** @test */
    public function orderRowsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('order_rows', [
                'id',
                'order_id',
                'fortnox_article_id',
                'description',
                'quantity',
                'unit',
                'price',
                'discount_percentage',
                'vat',
                'has_rut',
                'internal_note',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function orderRowHasPriceWithVat(): void
    {
        $orderRow = OrderRow::first();

        if ($orderRow) {
            $this->assertIsFloat($orderRow->price_with_vat);
        } else {
            $this->assertNull($orderRow);
        }
    }

    /** @test */
    public function orderRowHasOrder(): void
    {
        $orderRow = OrderRow::first();

        if ($orderRow) {
            $this->assertInstanceOf(Order::class, $orderRow->order);
        } else {
            $this->assertNull($orderRow);
        }
    }
}
