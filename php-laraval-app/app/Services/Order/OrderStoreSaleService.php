<?php

namespace App\Services\Order;

use App\Enums\Invoice\InvoiceCategoryEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\StoreSale;

class OrderStoreSaleService
{
    /**
     * Create order based on store sale.
     *
     * @param  StoreSale  $storeSale
     * @param  int  $userId
     * @param  int  $customerId
     * @return array{0:Order,1:Invoice}
     */
    public function createOrder($storeSale, $userId, $customerId)
    {
        $invoice = Invoice::findOrCreate(
            $userId,
            $customerId,
            $storeSale->created_at->month,
            $storeSale->created_at->year,
            InvoiceTypeEnum::Laundry(),
            InvoiceCategoryEnum::CashInvoice(),
        );

        $order = $storeSale->order()->create([
            'user_id' => $userId,
            'customer_id' => $customerId,
            'invoice_id' => $invoice->id,
            'status' => OrderStatusEnum::Draft(),
            'ordered_at' => $storeSale->created_at,
        ]);

        return [$order, $invoice];
    }

    /**
     * Create order row based on store sale.
     *
     * @param  Order  $order
     * @param  StoreSale  $storeSale
     */
    public function createOrderRows($order, $storeSale)
    {
        $rows = [];

        foreach ($storeSale->products as $product) {
            $rows[] = [
                'description' => $product->name,
                'fortnox_article_id' => $product->product->fortnox_article_id,
                'quantity' => $product->quantity,
                'unit' => $product->product->unit,
                'price' => $product->price,
                'discount_percentage' => $product->discount,
                'vat' => $product->vat_group,
                'has_rut' => false,
            ];
        }

        if (abs($storeSale->round_amount) > 0) {
            $rows[] = $this->getRoundAmountRow($storeSale);
        }

        $order->rows()->createMany($rows);
    }

    /**
     * Get round amount row for the order.
     *
     * @param  StoreSale  $storeSale
     */
    private function getRoundAmountRow($storeSale)
    {
        return [
            'description' => 'Rundning',
            'fortnox_article_id' => null,
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $storeSale->round_amount,
            'discount_percentage' => 0,
            'vat' => VatNumbersEnum::Zero(),
            'has_rut' => false,
        ];
    }
}
