<?php

namespace Tests\Model;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderFixedPrice;
use App\Models\OrderRow;
use App\Models\ScheduleCleaning;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderTest extends TestCase
{
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // $schedule = ScheduleCleaning::first();
        // $this->order = Order::create([
        //     'orderable_type' => ScheduleCleaning::class,
        //     'orderable_id' => $schedule->id,
        //     'user_id' => 1,
        //     'customer_id' => $schedule->customer_id,
        //     'service_id' => $schedule->subscription->service_id,
        //     'subscription_id' => $schedule->subscription_id,
        //     'invoice_id' => 1,
        //     'status' => OrderStatusEnum::Draft(),
        //     'paid_by' => now(),
        //     'paid_at' => now(),
        //     'ordered_at' => now(),
        // ]);

        // $this->order->rows()->create([
        //     'description' => $schedule->subscription->service->name,
        //     'fortnox_article_id' => $schedule->subscription->service->fortnox_article_id,
        //     'quantity' => $schedule->subscription->quarters,
        //     'unit' => ProductUnitEnum::Piece(),
        //     'price' => $schedule->subscription->service->price,
        //     'discount_percentage' => 0,
        //     'vat' => 25,
        //     'has_rut' => $schedule->subscription->service->has_rut,
        // ]);
    }

    /** @test */
    public function ordersDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('orders', [
                'id',
                'orderable_type',
                'orderable_id',
                'user_id',
                'customer_id',
                'service_id',
                'subscription_id',
                'invoice_id',
                'order_fixed_price_id',
                'status',
                'paid_by',
                'paid_at',
                'ordered_at',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    // /** @test */
    // public function orderHasUser(): void
    // {
    //     $order = Order::first();

    //     if ($order) {
    //         $this->assertInstanceOf(User::class, $order->user);
    //     } else {
    //         $this->assertNull($order);
    //     }
    // }

    // /** @test */
    // public function orderHasCustomer(): void
    // {
    //     if ($this->order) {
    //         $this->assertInstanceOf(Customer::class, $this->order->customer);
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }

    // /** @test */
    // public function orderHasService(): void
    // {
    //     if ($this->order) {
    //         $this->assertInstanceOf(Service::class, $this->order->service);
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }

    // /** @test */
    // public function orderHasRows(): void
    // {
    //     if ($this->order) {
    //         $this->assertIsObject($this->order->rows);
    //         $this->assertInstanceOf(OrderRow::class, $this->order->rows->first());
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }

    // /** @test */
    // public function orderHasOrderable(): void
    // {
    //     if ($this->order) {
    //         $this->assertIsObject($this->order->orderable);
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }

    // /** @test */
    // public function orderHasInvoice(): void
    // {
    //     if ($this->order) {
    //         $this->assertInstanceOf(Invoice::class, $this->order->invoice);
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }

    // /** @test */
    // public function orderHasSubscription(): void
    // {
    //     if ($this->order) {
    //             $this->assertInstanceOf(Subscription::class, $this->order->subscription);
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }

    // /** @test */
    // public function orderHasFixedPrice(): void
    // {
    //     $fixedPrice = OrderFixedPrice::create([]);
    //     $this->order->update([
    //         'order_fixed_price_id' => $fixedPrice->id,
    //     ]);

    //     if ($this->order) {
    //         $this->assertInstanceOf(OrderFixedPrice::class, $this->order->fixedPrice);
    //     } else {
    //         $this->assertNull($this->order);
    //     }
    // }
}
