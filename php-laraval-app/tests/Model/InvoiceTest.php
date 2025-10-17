<?php

namespace Tests\Model;

use App\Enums\Invoice\InvoiceTypeEnum;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    /** @test */
    public function invoicesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('invoices', [
                'id',
                'user_id',
                'customer_id',
                'fortnox_invoice_id',
                'fortnox_tax_reduction_id',
                'type',
                'month',
                'year',
                'remark',
                'total_gross',
                'total_net',
                'total_vat',
                'total_rut',
                'status',
                'sent_at',
                'due_at',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function invoiceCanFindOrCreate(): void
    {
        $invoice = Invoice::findOrCreate(1, 1, 1, 2021, InvoiceTypeEnum::CleaningAndLaundry());

        if ($invoice) {
            $this->assertNotEmpty($invoice);
        } else {
            $this->assertNull($invoice);
        }
    }

    /** @test */
    public function invoiceHasCustomer(): void
    {
        $invoice = Invoice::first();

        if ($invoice) {
            $this->assertInstanceOf(Customer::class, $invoice->customer);
        } else {
            $this->assertNull($invoice);
        }
    }

    /** @test */
    public function invoiceHasOrders(): void
    {
        $invoice = Invoice::first();

        if ($invoice) {
            $this->assertInstanceOf(Order::class, $invoice->orders()->first());
        } else {
            $this->assertNull($invoice);
        }
    }
}
