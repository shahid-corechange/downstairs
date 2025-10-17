<?php

namespace App\Services;

use App\Enums\Invoice\InvoiceCategoryEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Models\LaundryOrder;
use App\Models\Order;

class InvoiceService
{
    /**
     * Split cleaning invoice to cleaning and laundry invoice
     * and update invoice type to cleaning.
     */
    public function split(Invoice $invoice)
    {
        // Create laundry invoice
        $laundryInvoice = Invoice::create([
            'user_id' => $invoice->user_id,
            'customer_id' => $invoice->customer_id,
            'type' => InvoiceTypeEnum::Laundry(),
            'category' => InvoiceCategoryEnum::Invoice(),
            'month' => $invoice->month,
            'year' => $invoice->year,
            'status' => $invoice->status,
            'sent_at' => $invoice->sent_at,
            'due_at' => $invoice->due_at,
        ]);

        // Use laundry invoice in order that has laundry order type
        Order::where('invoice_id', $invoice->id)
            ->where('orderable_type', LaundryOrder::class)
            ->update([
                'invoice_id' => $laundryInvoice->id,
                'order_fixed_price_id' => null,
            ]);

        // Update invoice type to cleaning
        $invoice->update(['type' => InvoiceTypeEnum::Cleaning()]);

        // Update fixed price
        $invoice->orders()->update([
            'order_fixed_price_id' => null,
        ]);
    }

    /**
     * Merge laundry invoice to cleaning invoice
     * and update invoice type to cleaning and laundry.
     */
    public function merge(Invoice $invoice)
    {
        $laundryInvoice = Invoice::where('customer_id', $invoice->customer_id)
            ->where('category', InvoiceCategoryEnum::Invoice())
            ->open(InvoiceTypeEnum::Laundry())
            ->first();
        $fixedPrice = FixedPrice::getCleaningAndLaundry(
            $invoice->user_id,
            now()->month($invoice->month)->year($invoice->year)->startOfMonth(),
            now()->month($invoice->month)->year($invoice->year)->endOfMonth(),
        );

        $orderFixedPrice = $fixedPrice ?
            OrderFixedPriceService::findOrCreate($fixedPrice, $invoice->month, $invoice->year) :
            null;

        if ($laundryInvoice) {
            Order::where('invoice_id', $laundryInvoice->id)
                ->update([
                    'invoice_id' => $invoice->id,
                    'order_fixed_price_id' => $orderFixedPrice ? $orderFixedPrice->id : null,
                ]);

            $laundryInvoice->forceDelete();
        }

        $invoice->update(['type' => InvoiceTypeEnum::CleaningAndLaundry()]);
    }
}
