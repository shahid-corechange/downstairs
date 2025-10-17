<?php

namespace App\Services\Order;

use App\Enums\Invoice\InvoiceCategoryEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Models\LaundryOrder;
use App\Models\Order;
use App\Services\FixedPriceService;
use App\Services\InvoiceSummationService;
use App\Services\OrderFixedPriceService;
use DB;

class OrderStoreLaundryService
{
    public function __construct(
        private InvoiceSummationService $invoiceSummationService,
    ) {
    }

    /**
     * Create order based on store sale.
     *
     * @param  LaundryOrder  $laundryOrder
     * @return array{0:Order,1:Invoice}
     */
    public function createOrder($laundryOrder)
    {
        // Choose if the invoice is cash invoice or invoice
        if ($laundryOrder->payment_method === PaymentMethodEnum::Invoice()) {
            $type = Invoice::getUserType(
                $laundryOrder->user_id,
                $laundryOrder->customer->membership_type,
                InvoiceTypeEnum::Laundry()
            );

            $invoice = Invoice::findOrCreate(
                $laundryOrder->user_id,
                $laundryOrder->customer_id,
                $laundryOrder->created_at->month,
                $laundryOrder->created_at->year,
                $type,
            );
        } else {
            $invoice = Invoice::findOrCreate(
                $laundryOrder->user_id,
                $laundryOrder->customer_id,
                $laundryOrder->created_at->month,
                $laundryOrder->created_at->year,
                InvoiceTypeEnum::Laundry(),
                InvoiceCategoryEnum::CashInvoice(),
            );
        }

        $order = $laundryOrder->order()->create([
            'user_id' => $laundryOrder->user_id,
            'customer_id' => $laundryOrder->customer_id,
            'invoice_id' => $invoice->id,
            'status' => OrderStatusEnum::Draft(),
            'ordered_at' => $laundryOrder->created_at,
            'service_id' => $laundryOrder->subscription?->service_id,
            'subscription_id' => $laundryOrder->subscription_id,
        ]);

        $this->applyFixedPrice($order, $laundryOrder->subscription->fixedPrice);

        return [$order, $invoice];
    }

    /**
     * Create order row based on store sale.
     *
     * @param  Order  $order
     * @param  LaundryOrder  $laundryOrder
     */
    public function createOrderRows($order, $laundryOrder)
    {
        $rows = [];

        $isRut = $laundryOrder->customer->membership_type === MembershipTypeEnum::Company();

        $fixedPrice = $laundryOrder->subscription?->fixedPrice;
        $fixedPriceProductIds = $fixedPrice ? $fixedPrice->laundryProducts->pluck('id')->toArray() : [];

        foreach ($laundryOrder->products as $product) {
            // if product include in fixed price or fixed price include all products, skip create row
            // except product sales misc
            if ($fixedPrice &&
                (in_array($product->product_id, $fixedPriceProductIds) || empty($fixedPriceProductIds))
                && $product->product_id !== config('downstairs.products.productSalesMisc.id')
            ) {
                continue;
            }

            $rows[] = [
                'description' => $product->name,
                'fortnox_article_id' => $product->product->fortnox_article_id,
                'quantity' => $product->quantity,
                'unit' => $product->product->unit,
                'price' => $product->price,
                'discount_percentage' => $product->discount,
                'vat' => $product->vat_group,
                'has_rut' => $isRut ? false : $product->has_rut,
            ];
        }

        $rows[] = $this->getPreferenceRow($laundryOrder);

        if (abs($laundryOrder->round_amount) > 0) {
            $rows[] = $this->getRoundAmountRow($laundryOrder);
        }

        $order->rows()->createMany($rows);
    }

    /**
     * Update order rows based on laundry order products.
     * This will sync the order rows with the current state of laundry order products
     * while preserving other rows (from cleaning or manually created).
     *
     * @param  Order  $order
     * @param  LaundryOrder  $laundryOrder
     */
    public function updateOrderRows($order, $laundryOrder)
    {
        // Get current products with their fortnox article IDs
        $currentProducts = $laundryOrder->products->keyBy('product.fortnox_article_id');
        $currentFortnoxIds = $currentProducts->keys()->toArray();

        // Get existing rows that match the current products' fortnox IDs
        // These are likely to be our laundry order rows
        $existingRows = $order->rows()
            ->whereIn('fortnox_article_id', $currentFortnoxIds)
            ->get()
            ->keyBy('fortnox_article_id');

        DB::transaction(function () use (
            $order,
            $currentProducts,
            $existingRows,
            $currentFortnoxIds,
        ) {
            // Update or create rows for current products
            foreach ($currentProducts as $fortnoxArticleId => $product) {
                $rowData = [
                    'description' => $product->name,
                    'fortnox_article_id' => $fortnoxArticleId,
                    'quantity' => $product->quantity,
                    'unit' => $product->product->unit,
                    'price' => $product->price,
                    'discount_percentage' => $product->discount,
                    'vat' => $product->vat_group,
                    'has_rut' => $product->has_rut,
                ];

                if (isset($existingRows[$fortnoxArticleId])) {
                    // Only update if the values are different
                    $existingRow = $existingRows[$fortnoxArticleId];
                    $needsUpdate = false;

                    foreach ($rowData as $key => $value) {
                        if ($value != $existingRow->$key) {
                            $needsUpdate = true;
                            break;
                        }
                    }

                    if ($needsUpdate) {
                        $existingRow->update($rowData);
                    }
                } else {
                    // Create new row only if it doesn't exist
                    $order->rows()->create($rowData);
                }
            }

            // Find rows that were previously from this laundry order but are no longer needed
            // We identify these by matching fortnox IDs that are no longer in current products
            $rowsToDelete = $order->rows()
                ->whereIn('fortnox_article_id', $currentFortnoxIds)
                ->whereNotIn('id', $existingRows->pluck('id'))
                ->get();

            // Only delete rows that exactly match our laundry order product data
            foreach ($rowsToDelete as $row) {
                $matchingProduct = $currentProducts->get($row->fortnox_article_id);
                if (! $matchingProduct) {
                    // This row was from our laundry order but the product was removed
                    $row->delete();
                }
            }
        });
    }

    /**
     * Sync order rows with laundry order products.
     * This will update the order rows if the order exists.
     *
     * @param  LaundryOrder  $laundryOrder
     */
    public function syncOrderRows($laundryOrder)
    {
        $order = $laundryOrder->order;

        if ($order) {
            $this->updateOrderRows($order, $laundryOrder);
            $summation = $this->invoiceSummationService->getSummation($order->invoice);
            $order->invoice->update($summation);
        }
    }

    /**
     * Apply fixed price to the order.
     *
     * @param  Order  $order
     * @param  FixedPrice|null  $fixedPrice
     */
    public function applyFixedPrice($order, $fixedPrice)
    {
        if (! $fixedPrice || ! FixedPriceService::isApplicable($fixedPrice, $order->ordered_at)) {
            return;
        }

        if (! $fixedPrice->is_per_order) {
            // If fixed price is monthly, check if it can be reused.
            // Check if order fixed price for the same month and year exists.
            $orderFixedPrice = OrderFixedPriceService::getMonthlyFixedPrice(
                $fixedPrice,
                $order->ordered_at->month,
                $order->ordered_at->year,
            );

            if ($orderFixedPrice) {
                $order->update(['order_fixed_price_id' => $orderFixedPrice->id]);

                return;
            }
        }

        $orderFixedPrice = OrderFixedPriceService::fromFixedPrice($fixedPrice, $order);
        $order->update(['order_fixed_price_id' => $orderFixedPrice->id]);
    }

    /**
     * Get preference row for the order.
     *
     * @param  LaundryOrder  $laundryOrder
     */
    private function getPreferenceRow($laundryOrder)
    {
        return [
            'description' => $laundryOrder->preference->name,
            'fortnox_article_id' => null,
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $laundryOrder->preference_amount,
            'discount_percentage' => 0,
            'vat' => VatNumbersEnum::Zero(),
            'has_rut' => false,
        ];
    }

    /**
     * Get round amount row for the order.
     *
     * @param  LaundryOrder  $laundryOrder
     */
    private function getRoundAmountRow($laundryOrder)
    {
        return [
            'description' => 'Rundning',
            'fortnox_article_id' => null,
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $laundryOrder->round_amount,
            'discount_percentage' => 0,
            'vat' => VatNumbersEnum::Zero(),
            'has_rut' => false,
        ];
    }
}
