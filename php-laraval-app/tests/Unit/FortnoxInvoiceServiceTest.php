<?php

namespace Tests\Unit;

use App\DTOs\Fortnox\Invoice\InvoiceDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\Fortnox\FortnoxInvoiceService;
use App\Services\Fortnox\FortnoxService;
use App\Services\Fortnox\TransientFortnoxInvoiceService;
use App\Services\OrderService;
use Mockery;
use Tests\TestCase;

class FortnoxInvoiceServiceTest extends TestCase
{
    // For differentiate the fixed price laundry and cleaning
    // protected FortnoxInvoiceService $service;

    // protected $fortnoxServiceMock;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->user->subscriptions()->forceDelete();

    //     // Create a mock of the FortnoxService
    //     $this->fortnoxServiceMock = Mockery::mock(FortnoxService::class);

    //     // Set up the expectation that createInvoice will be called
    //     $this->fortnoxServiceMock->shouldReceive('createInvoice')->andReturn(InvoiceDTO::from([
    //         'document_number' => '123',
    //     ]));

    //     // Inject the mock into the class or method you are testing
    //     $this->service = new FortnoxInvoiceService($this->fortnoxServiceMock);

    //     $this->addArticleId();
    //     $this->createSubscriptions();
    //     $this->createOrder();
    // }

    private function addArticleId()
    {
        Product::where('id', config('downstairs.products.transport.id'))->update([
            'fortnox_article_id' => '205',
        ]);
        Product::where('id', config('downstairs.products.material.id'))->update([
            'fortnox_article_id' => '206',
        ]);
        Service::where('id', 1)->update([
            'fortnox_article_id' => '101',
        ]);
    }

    private function createSubscription(): Subscription
    {
        return Subscription::factory(1, [
            'team_id' => 1,
            'frequency' => SubscriptionFrequencyEnum::EveryWeek(),
            'start_at' => now()->format('Y-m-d'),
            'start_time_at' => now()->format('H:00:00'),
            'end_time_at' => now()->addHour()->format('H:00:00'),
            'quarters' => 4,
        ])->forUser($this->user)->create()[0];
    }

    private function createOrder(Subscription $subscription): Order
    {
        $service = new OrderService();
        $schedule = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        [$order] = $service->createOrder($schedule);
        $service->createOrderRows($order, $schedule);

        return $order;
    }

    private function createFixedPrice(Subscription $subscription, bool $hasRut): FixedPrice
    {
        $fixedPrice = FixedPrice::create([
            'user_id' => $subscription->user_id,
        ]);
        $subscription->update(['fixed_price_id' => $fixedPrice->id]);

        $fixedPrice->rows()->create([
            'type' => 'service',
            'quantity' => 1,
            'price' => (fake()->numberBetween(1, 10) * 100) / (1 + 25 / 100),
            'vat_group' => 25,
            'has_rut' => $hasRut,
        ]);

        return $fixedPrice;
    }

    // public function testCanCreateInvoiceWhitoutFixedPrice(): void
    // {
    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $this->service->create($invoice, $this->fortnoxServiceMock);
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();

    //     $this->assertNotNull($invoice->fortnox_invoice_id);
    //     $this->assertEquals(InvoiceStatusEnum::Created(), $invoice->status);
    //     $this->assertEquals(OrderStatusEnum::Progress(), $order->status);
    // }

    // public function testCanCreateInvoiceWithFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //         [
    //             'type' => FixedPriceRowTypeEnum::Transport(),
    //             'has_rut' => true,
    //         ],
    //         [
    //             'type' => FixedPriceRowTypeEnum::Material(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $this->service->create($invoice, $this->fortnoxServiceMock);
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();

    //     $this->assertNotNull($invoice->fortnox_invoice_id);
    //     $this->assertNotNull($order->fixedPrice);
    //     $this->assertCount(3, $order->fixedPrice->rows);
    // }

    // public function testCanCreateInvoiceWithOnlyCleaningFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $this->service->create($invoice, $this->fortnoxServiceMock);
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();

    //     $this->assertNotNull($invoice->fortnox_invoice_id);
    //     $this->assertNotNull($order->fixedPrice);
    //     $this->assertCount(1, $order->fixedPrice->rows);
    // }

    // public function testCanCreateInvoiceWithOnlyCleaningAndLaundryFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::CleaningAndLaundry(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $this->service->create($invoice, $this->fortnoxServiceMock);
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();

    //     $this->assertNotNull($invoice->fortnox_invoice_id);
    //     $this->assertNotNull($order->fixedPrice);
    //     $this->assertCount(1, $order->fixedPrice->rows);
    // }

    // public function testCanCreateInvoiceWithOnlyTransportFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Transport(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $this->service->create($invoice, $this->fortnoxServiceMock);
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();

    //     $this->assertNotNull($invoice->fortnox_invoice_id);
    //     $this->assertNotNull($order->fixedPrice);
    //     $this->assertCount(1, $order->fixedPrice->rows);
    // }

    // public function testCanCreateInvoiceWithOnlyMaterialFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Material(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $this->service->create($invoice, $this->fortnoxServiceMock);
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();

    //     $this->assertNotNull($invoice->fortnox_invoice_id);
    //     $this->assertNotNull($order->fixedPrice);
    //     $this->assertCount(1, $order->fixedPrice->rows);
    // }

    // public function testCanGetInvoiceRowsWithFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //         [
    //             'type' => FixedPriceRowTypeEnum::Transport(),
    //             'has_rut' => false,
    //         ],
    //         [
    //             'type' => FixedPriceRowTypeEnum::Material(),
    //             'has_rut' => false,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(4, $rows);
    // }

    // public function testCanGetInvoiceRowsWithOnlyCleaningFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(5, $rows);
    // }

    // public function testCanGetInvoiceRowsWithOnlyCleaningAndLaundryFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::CleaningAndLaundry(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(5, $rows);
    // }

    // public function testCanGetInvoiceRowsWithOnlyTransportFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Transport(),
    //             'has_rut' => false,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(5, $rows);
    // }

    // public function testCanGetInvoiceRowsWithOnlyMaterialFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Material(),
    //             'has_rut' => false,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(5, $rows);
    // }

    // public function testCanGetInvoiceRowsWithCleaningAndMaterialFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //         [
    //             'type' => FixedPriceRowTypeEnum::Material(),
    //             'has_rut' => false,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(5, $rows);
    // }

    // public function testCanGetInvoiceRowsWithCleaningAndTransportFixedPrice(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //         [
    //             'type' => FixedPriceRowTypeEnum::Transport(),
    //             'has_rut' => false,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(5, $rows);
    // }

    // public function testCanGetInvoiceRowsWithFixedPriceAndAdditionalRows(): void
    // {
    //     $this->createFixedPrice([
    //         [
    //             'type' => FixedPriceRowTypeEnum::Cleaning(),
    //             'has_rut' => true,
    //         ],
    //     ]);

    //     /** @var Invoice $invoice */
    //     $invoice = $this->user->customers()->first()->invoices()->first();
    //     /** @var Order $order */
    //     $order = $invoice->orders()->first();
    //     $order->rows()->createMany([
    //         [
    //             'description' => 'Additional row',
    //             'quantity' => 1,
    //             'unit' => ProductUnitEnum::Piece(),
    //             'price' => 100,
    //             'vat' => 25,
    //             'discount_percentage' => 0,
    //             'has_rut' => false,
    //         ],
    //         [
    //             'description' => 'Additional row 2',
    //             'quantity' => 1,
    //             'unit' => ProductUnitEnum::Piece(),
    //             'price' => 200,
    //             'vat' => 25,
    //             'discount_percentage' => 0,
    //             'has_rut' => false,
    //         ],
    //     ]);
    //     $rows = $this->service->getInvoiceRows($invoice);

    //     $this->assertCount(7, $rows);
    // }

    public function testExtraRowsDidNotOverridedByFixedPrice(): void
    {
        $service = new TransientFortnoxInvoiceService();

        $this->addArticleId();
        $subscription = $this->createSubscription();
        $order = $this->createOrder($subscription);
        $fixedPrice = $this->createFixedPrice($subscription, true);

        $order->rows()->create([
            'description' => 'Extra row',
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'vat' => 25,
            'discount_percentage' => 0,
            'has_rut' => false,
        ]);

        $rows = $service->getRowsFromOrder($order, $fixedPrice);

        $this->assertCount(1, $rows);
        $this->assertEquals('Extra row', $rows[0]['description']);
    }
}
