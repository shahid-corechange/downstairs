<?php

namespace Tests\Portal\Operation;

use App\DTOs\Fortnox\Invoice\InvoiceDTO;
use App\DTOs\Invoice\InvoiceResponseDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderPaidByEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Jobs\CreateTaxReductionJob;
use App\Models\CustomerDiscount;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\TaxReductionService;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        $invoice = Invoice::first();

        $newInvoice = Invoice::findOrCreate(
            $invoice->user_id,
            $invoice->customer_id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

        $newInvoice->orders()->create([
            'user_id' => $newInvoice->user_id,
            'customer_id' => $newInvoice->customer_id,
            'status' => OrderStatusEnum::Draft(),
            'type' => InvoiceTypeEnum::Cleaning(),
            'paid_by' => OrderPaidByEnum::Invoice(),
            'orderable_type' => Invoice::class,
            'orderable_id' => $newInvoice->id,
            'ordered_at' => $now,
        ]);
    }

    public function testAdminCanAccessInvoices(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Invoice::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/invoices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoice/Overview/index')
                ->has('invoices', $total)
                ->has('invoices.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('month')
                    ->has('year')
                    ->has('sentAt')
                    ->has('dueAt')
                    ->has('status')
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc())
                    ->has('customer', fn (Assert $page) => $page
                        ->has('membershipType')
                        ->has('dueDays')
                        ->etc())
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessInvoices(): void
    {
        $this->actingAs($this->user)
            ->get('/invoices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterInvoices(): void
    {
        $data = Invoice::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/invoices?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoice/Overview/index')
                ->has('invoices', 1)
                ->has('invoices.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('month', $data->month)
                    ->where('year', $data->year)
                    ->where('status', $data->status)
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc())
                    ->has('customer', fn (Assert $page) => $page
                        ->has('membershipType')
                        ->has('dueDays')
                        ->etc())
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessInvoicesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/json');
        $keys = array_keys(
            InvoiceResponseDTO::from(Invoice::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanUpdateInvoice(): void
    {
        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        /** @var Order $order */
        $order = $invoice->orders()->first();
        // add order fixed price
        $orderFixedPrice = $order->fixedPrice()->create([
            'fixed_price_id' => FixedPrice::first()->id,
            'is_per_order' => true,
        ]);
        $row1 = $order->rows()->create([
            'description' => 'test product',
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'vat' => 25,
            'has_rut' => false,
            'discount_percentage' => 0,
        ]);
        $row2 = $order->rows()->create([
            'description' => 'test product 2',
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'vat' => 25,
            'has_rut' => false,
            'discount_percentage' => 0,
        ]);
        $date = now()->addDays(5);
        $data = [
            'sentAt' => $date->format('Y-m-d'),
            'remark' => 'test remark',
            'rows' => [
                [
                    'type' => 'order',
                    'parentId' => $order->id,
                    'description' => 'new product',
                    'quantity' => 1,
                    'unit' => ProductUnitEnum::Piece(),
                    'price' => 100,
                    'vat' => 25,
                    'hasRut' => false,
                    'discountPercentage' => 0,
                ],
                [
                    'type' => 'fixed price',
                    'parentId' => $orderFixedPrice->id,
                    'description' => 'new fixed price',
                    'quantity' => 1,
                    'unit' => ProductUnitEnum::Piece(),
                    'price' => 200,
                    'vat' => 25,
                    'hasRut' => false,
                    'discountPercentage' => 0,
                ],
                [
                    'id' => $row2->id,
                    'type' => 'order',
                    'parentId' => $order->id,
                    'description' => 'new order 2',
                    'quantity' => 1,
                    'unit' => ProductUnitEnum::Piece(),
                    'price' => 300,
                    'vat' => 25,
                    'hasRut' => true,
                    'discountPercentage' => 0,
                ],
            ],
        ];
        $this->actingAs($this->admin)
            ->patch("/invoices/$invoice->id", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('invoice updated successfully'));

        $dueAt = $date->copy()->addDays($invoice->customer->due_days);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'sent_at' => $data['sentAt'],
            'due_at' => $dueAt->format('Y-m-d'),
            'remark' => $data['remark'],
        ]);

        $this->assertDatabaseMissing('order_rows', [
            'id' => $row1->id,
        ]);

        $this->assertDatabaseHas('order_rows', [
            'id' => $row2->id,
            'description' => 'new order 2',
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 300,
            'vat' => 25,
            'has_rut' => true,
            'discount_percentage' => 0,
        ]);

        $this->assertDatabaseHas('order_rows', [
            'description' => 'new product',
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'vat' => 25,
            'has_rut' => false,
            'discount_percentage' => 0,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('order_fixed_price_rows', [
            'order_fixed_price_id' => $orderFixedPrice->id,
            'description' => 'new fixed price',
            'quantity' => 1,
            'price' => 200,
            'vat_group' => 25,
            'has_rut' => false,
        ]);
    }

    public function testCanNotUpdateInvoice(): void
    {
        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $invoice->update(['status' => InvoiceStatusEnum::Paid()]);
        $date = now()->addDays(5);
        $data = [
            'sentAt' => $date->format('Y-m-d'),
            'dueAt' => $date->copy()->addDays(5)->format('Y-m-d'),
        ];
        $this->actingAs($this->admin)
            ->patch("/invoices/$invoice->id", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('invoice status not open'));
    }

    public function testCanCreateFortnoxInvoice(): void
    {
        $invoiceReturn = InvoiceDTO::from([
            'document_number' => '123456',
        ]);

        // Create a mock instance of the FortnoxCustomerService
        $fortnoxServiceMock = $this->mock(FortnoxCustomerService::class);

        // Mock the method of the FortnoxCustomerService to return a specific value
        $fortnoxServiceMock->shouldReceive('createInvoice')->andReturn($invoiceReturn);

        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/create")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('invoice created successfully'));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatusEnum::Created(),
            'fortnox_invoice_id' => $invoiceReturn->document_number,
        ]);

        if ($invoice->orders->first()) {
            $this->assertDatabaseHas('orders', [
                'id' => $invoice->orders->first()->id,
                'status' => OrderStatusEnum::Progress(),
            ]);
        }
        $this->assertDatabaseHas('orders', [
            'id' => $invoice->orders->first()->id,
            'status' => OrderStatusEnum::Progress(),
        ]);

        $userId = $invoice->user_id;

        $isUseCleaningDiscount = $invoice->orders()->cleaning()->draft()->exists();
        if ($isUseCleaningDiscount) {
            $this->assertTrue(CustomerDiscount::shouldHaveReceived('useDiscount')
                ->with($userId, CustomerDiscountTypeEnum::Cleaning())->once());
        }

        $isUseLaundryDiscount = $invoice->orders()->laundry()->draft()->exists();
        if ($isUseLaundryDiscount) {
            $this->assertTrue(CustomerDiscount::shouldHaveReceived('useDiscount')
                ->with($userId, CustomerDiscountTypeEnum::Laundry())->once());
        }

        if ($invoice->customer->membership_type === MembershipTypeEnum::Private()) {
            Bus::assertDispatchedSync(CreateTaxReductionJob::class);
        }
    }

    public function testCanNotCreateFortnoxInvoice(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $this->mock(FortnoxCustomerService::class);
        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $invoice->update(['status' => InvoiceStatusEnum::Paid()]);
        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/create")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('invoice already created'));
    }

    public function testCanCancelFortnoxInvoice(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $fortnoxServiceMock = $this->mock(FortnoxCustomerService::class);

        // Mock the method of the FortnoxCustomerService to return a specific value
        $fortnoxServiceMock->shouldReceive('cancelInvoice')
            ->andReturn(Response(['success' => 'success'], 200));

        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $invoice->update([
            'status' => InvoiceStatusEnum::Created(),
            'fortnox_invoice_id' => 123456,
        ]);

        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/cancel")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('invoice canceled successfully'));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatusEnum::Cancel(),
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $invoice->orders->first()->id,
            'status' => OrderStatusEnum::Cancel(),
        ]);
    }

    public function testCanNotCancelFortnoxInvoice(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $this->mock(FortnoxCustomerService::class);
        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $invoice->update(['status' => InvoiceStatusEnum::Paid()]);
        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/cancel")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('invoice not created'));
    }

    public function testCanSendFortnoxInvoice(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $fortnoxServiceMock = $this->mock(FortnoxCustomerService::class);

        // Mock the method of the FortnoxCustomerService to return a specific value
        $fortnoxServiceMock->shouldReceive('bookkeepInvoice')
            ->andReturn(Response(['success' => 'success'], 200));

        // Create a mock instance of the TaxReductionService
        $taxReductionServiceMock = $this->mock(TaxReductionService::class);

        // Mock the method of the TaxReductionService to return a specific value
        $taxReductionServiceMock->shouldReceive('get')
            ->andReturn([
                'tax_reduction' => 123456,
                'co_applicant_tax_reduction' => 123456,
            ]);

        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $swedishTime = now()->setTimezone('Europe/Stockholm');

        if (! $invoice->sent_at->isSameDay($swedishTime)) {
            // Mock the method of the FortnoxCustomerService to return a specific value
            $fortnoxServiceMock->shouldReceive('updateInvoice')
                ->andReturn(InvoiceDTO::from([]));
        }

        $invoice->update([
            'status' => InvoiceStatusEnum::Created(),
            'fortnox_invoice_id' => 123456,
        ]);

        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/send")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('invoice sent successfully'));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'fortnox_tax_reduction_id' => 123456,
            'status' => InvoiceStatusEnum::Sent(),
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $invoice->orders->first()->id,
            'status' => OrderStatusEnum::Done(),
        ]);
    }

    public function testCanNotSendFortnoxInvoiceIfNotCreated(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $this->mock(FortnoxCustomerService::class);
        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $invoice->update(['status' => InvoiceStatusEnum::Paid()]);
        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/send")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('invoice not created'));
    }

    public function testCanNotSendFortnoxInvoiceIfAlreadyCanceled(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $this->mock(FortnoxCustomerService::class);
        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $invoice->update(['status' => InvoiceStatusEnum::Cancel()]);
        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/send")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('invoice already canceled'));
    }

    public function testCanNotSendFortnoxInvoiceIfSentFailed(): void
    {
        // Create a mock instance of the FortnoxCustomerService
        $fortnoxServiceMock = $this->mock(FortnoxCustomerService::class);

        // Mock the method of the FortnoxCustomerService to return a specific value
        $fortnoxServiceMock->shouldReceive('bookkeepInvoice')
            ->andReturn(Response(['error' => 'error'], 400));

        $invoice = Invoice::open(InvoiceTypeEnum::Cleaning())->first();
        $swedishTime = now()->setTimezone('Europe/Stockholm');

        if (! $invoice->sent_at->isSameDay($swedishTime)) {
            // Mock the method of the FortnoxCustomerService to return a specific value
            $fortnoxServiceMock->shouldReceive('updateInvoice')
                ->andReturn(InvoiceDTO::from([]));
        }

        $invoice->update([
            'status' => InvoiceStatusEnum::Created(),
            'fortnox_invoice_id' => 123456,
        ]);
        $this->actingAs($this->admin)
            ->post("/invoices/$invoice->id/send")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('invoice sent failed'));
    }
}
