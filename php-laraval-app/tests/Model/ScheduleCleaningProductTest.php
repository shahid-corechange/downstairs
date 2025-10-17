<?php

namespace Tests\Model;

use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningProduct;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleCleaningProductTest extends TestCase
{
    // /** @test */
    // public function scheduleCleaningProductsDatabaseHasExpectedColumns(): void
    // {
    //     $this->assertTrue(
    //         Schema::hasColumns('schedule_cleaning_products', [
    //             'id',
    //             'schedule_cleaning_id',
    //             'product_id',
    //             'price',
    //             'quantity',
    //             'discount_percentage',
    //             'payment_method',
    //             'created_at',
    //             'updated_at',
    //         ]),
    //     );
    // }

    // /** @test */
    // public function scheduleCleaningProductHasSchedule(): void
    // {
    //     $scheduleProduct = ScheduleCleaningProduct::first();

    //     $this->assertInstanceOf(ScheduleCleaning::class, $scheduleProduct->schedule);
    // }

    // /** @test */
    // public function scheduleCleaningProductHasProduct(): void
    // {
    //     $scheduleProduct = ScheduleCleaningProduct::first();

    //     $this->assertInstanceOf(Product::class, $scheduleProduct->product);
    // }
}
