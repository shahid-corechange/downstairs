<?php

namespace App\Services;

use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\VatNumbersEnum;
use App\Models\CustomerDiscount;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Models\OldOrder;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningProduct;

class OrderService
{
    /**
     * Create order based on schedule cleaning.
     *
     * @deprecated Use OrderCleaningService and OrderLaundryService instead.
     *
     * @param  Schedule  $schedule
     * @param  bool  $isCredited
     * @return array{0:Order,1:Invoice}
     */
    public function createOrder($schedule, $isCredited = false)
    {
        /** @var \App\Models\Subscription */
        $subscription = $schedule->scheduleable;
        $type = Invoice::getUserType(
            $subscription->user_id,
            $subscription->customer->membership_type,
            InvoiceTypeEnum::Cleaning()
        );

        $invoice = Invoice::findOrCreate(
            $schedule->user_id,
            $schedule->customer_id,
            $schedule->start_at->month,
            $schedule->start_at->year,
            $type,
        );

        $order = $schedule->order()->create([
            'user_id' => $schedule->user_id,
            'customer_id' => $schedule->customer_id,
            'service_id' => $schedule->service_id,
            'invoice_id' => $invoice->id,
            // 'subscription_id' => $schedule->scheduleable_id,
            'status' => OrderStatusEnum::Draft(),
            'ordered_at' => $schedule->start_at,
        ]);

        $this->applyFixedPrice($order, $subscription->fixedPrice, $isCredited);

        if ($isCredited && ! $invoice->remark) {
            $invoice->update([
                'remark' => '*Pga. sen avbokning utgår full debitering men '.
                    'motsvarande summa/tid i krediter finns att använda i appen.',
            ]);
        }

        return [$order, $invoice];
    }

    /**
     * Create order row based on schedule cleaning.
     *
     * @deprecated Use OrderCleaningService and OrderLaundryService instead.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<array-key, ScheduleCleaningProduct>|null  $products
     */
    public function createOrderRows(
        Order $order,
        ScheduleCleaning $cleaning,
        $products = null,
    ) {
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $cleaning->subscription->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );
        $rows = $this->getService($cleaning, $discount);
        $cleaningProducts = $products ?? $cleaning->products;

        foreach ($cleaningProducts as $product) {
            $rows[] = [
                'description' => $this->getName($product),
                'fortnox_article_id' => $product->product->fortnox_article_id,
                'quantity' => 1,
                'unit' => $product->product->unit,
                'price' => $product->price,
                'discount_percentage' => max($product->discount_percentage, $discount ? $discount->value : 0),
                'vat' => $product->product->vat_group,
                'has_rut' => $product->product->has_rut,
            ];
        }

        $rows[] = $this->getTransport($discount);
        $rows[] = $this->getMaterial($cleaning, $discount);

        $order->rows()->createMany($rows);
        DiscountService::useDiscount($discount);
    }

    /**
     * Create order row when cancel by customer (refund).
     *
     * @deprecated Use OrderCleaningService and OrderLaundryService instead.
     */
    public function cancelByCustomer(
        Order $order,
        Schedule $schedule,
    ): void {
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $schedule->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );
        $name = $schedule->service->name;

        $order->rows()->create([
            'description' => $name.get_credit_refund_description(),
            'fortnox_article_id' => $schedule->service->fortnox_article_id,
            'quantity' => $schedule->quarters,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $schedule->service->price,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'has_rut' => $schedule->service->has_rut,
        ]);
        DiscountService::useDiscount($discount);
    }

    /**
     * Create order row when cancel by admin (refund).
     *
     * @deprecated Use OrderCleaningService and OrderLaundryService instead.
     */
    public function cancelByAdmin(
        Order $order,
        Schedule $schedule,
    ): void {
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $schedule->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );
        $isPrivate = $schedule->customer?->membership_type === MembershipTypeEnum::Private();

        $order->rows()->create([
            'description' => $schedule->service->name.
                get_credit_refund_description(),
            'fortnox_article_id' => $schedule->service->fortnox_article_id,
            'quantity' => $schedule->quarters,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $schedule->service->price,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'has_rut' => $isPrivate ? $schedule->service->has_rut : false,
        ]);
        DiscountService::useDiscount($discount);
    }

    /**
     * Apply fixed price to the order.
     */
    public function applyFixedPrice(Order $order, ?FixedPrice $fixedPrice, bool $isCredited = false)
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

        $orderFixedPrice = OrderFixedPriceService::fromFixedPrice($fixedPrice, $order, $isCredited);
        $order->update(['order_fixed_price_id' => $orderFixedPrice->id]);
    }

    /**
     * Get the total gross amount for the order.
     *
     * Gross amount is the total amount excluding VAT and discount.
     *
     * @param  Order  $order
     * @param  array<int, int>|null  $fixedPriceArticleIds
     * @return float
     */
    public function getTotalGrossAmount($order, $fixedPriceArticleIds = null)
    {
        $totalAmount = 0.0;

        if ($order->orderable_type === OldOrder::class && $order->order_fixed_price_id) {
            return $totalAmount;
        }

        if ($order->order_fixed_price_id && $order->fixedPrice->is_per_order) {
            $totalAmount += $order->fixedPrice->total_gross_amount;
        }

        $fixedPriceArticleIds = $fixedPriceArticleIds ?? get_fixed_price_article_ids($order);

        $totalAmount += $order->rows->sum(function ($row) use ($fixedPriceArticleIds) {
            if (in_array($row->fortnox_article_id, $fixedPriceArticleIds)) {
                return 0;
            }

            return $row->total_gross_amount;
        });

        return $totalAmount;
    }

    /**
     * Get the total net amount for the order.
     *
     * Net amount is the total amount including discount but excluding VAT.
     *
     * @param  Order  $order
     * @param  array<int, int>|null  $fixedPriceArticleIds
     * @return float
     */
    public function getTotalNetAmount($order, $fixedPriceArticleIds = null)
    {
        $totalAmount = 0.0;

        if ($order->orderable_type === OldOrder::class && $order->order_fixed_price_id) {
            return $totalAmount;
        }

        if ($order->order_fixed_price_id && $order->fixedPrice->is_per_order) {
            $totalAmount += $order->fixedPrice->total_net_amount;
        }

        $fixedPriceArticleIds = $fixedPriceArticleIds ?? get_fixed_price_article_ids($order);

        $totalAmount += $order->rows->sum(function ($row) use ($fixedPriceArticleIds) {
            if (in_array($row->fortnox_article_id, $fixedPriceArticleIds)) {
                return 0;
            }

            return $row->total_net_amount;
        });

        return $totalAmount;
    }

    /**
     * Get the total VAT amount for the order.
     *
     * @param  Order  $order
     * @param  array<int, int>|null  $fixedPriceArticleIds
     * @return float
     */
    public function getTotalVatAmount($order, $fixedPriceArticleIds = null)
    {
        $totalAmount = 0.0;

        if ($order->orderable_type === OldOrder::class && $order->order_fixed_price_id) {
            return $totalAmount;
        }

        if ($order->order_fixed_price_id && $order->fixedPrice->is_per_order) {
            return $order->fixedPrice->total_vat_amount;
        }

        $fixedPriceArticleIds = $fixedPriceArticleIds ?? get_fixed_price_article_ids($order);

        $totalAmount += $order->rows->sum(function ($row) use ($fixedPriceArticleIds) {
            if (in_array($row->fortnox_article_id, $fixedPriceArticleIds)) {
                return 0;
            }

            return $row->vat_amount;
        });

        return $totalAmount;
    }

    /**
     * Get the total amount for the order including VAT and discount
     *
     * @param  Order  $order
     * @param  array<int, int>|null  $fixedPriceArticleIds
     * @return float
     */
    public function getTotalAmount($order, $fixedPriceArticleIds = null)
    {
        $totalAmount = 0.0;

        if ($order->orderable_type === OldOrder::class && $order->order_fixed_price_id) {
            return $totalAmount;
        }

        if ($order->order_fixed_price_id && $order->fixedPrice->is_per_order) {
            $totalAmount += $order->fixedPrice->total_fixed_price;
        }

        $fixedPriceArticleIds = $fixedPriceArticleIds ?? get_fixed_price_article_ids($order);

        $totalAmount += $order->rows->sum(function ($row) use ($fixedPriceArticleIds) {
            if (in_array($row->fortnox_article_id, $fixedPriceArticleIds)) {
                return 0;
            }

            return $row->total_amount;
        });

        return $totalAmount;
    }

    /**
     * Get the total RUT amount for the order.
     *
     * @param  Order  $order
     * @param  string  $invoiceType
     * @param  array<int, int>|null  $fixedPriceArticleIds
     * @return float
     */
    public function getTotalRutAmount($order, $invoiceType, $fixedPriceArticleIds = null)
    {
        $totalRutAmount = 0.0;

        if ($order->orderable_type === OldOrder::class && $order->order_fixed_price_id) {
            return $totalRutAmount;
        }

        if ($order->order_fixed_price_id && $order->fixedPrice->is_per_order) {
            $totalRutAmount += $order->fixedPrice->total_rut_amount;
        }

        $isLaundry = $invoiceType === InvoiceTypeEnum::Laundry();
        $rutPercentage = $isLaundry ? 0.25 : 0.50; // 25% for laundry, 50% for others

        $fixedPriceArticleIds = $fixedPriceArticleIds ?? get_fixed_price_article_ids($order);

        $totalRutAmount += $order->rows->sum(function ($row) use ($rutPercentage, $fixedPriceArticleIds) {
            if (in_array($row->fortnox_article_id, $fixedPriceArticleIds) || ! $row->has_rut) {
                return 0;
            }

            return $row->total_amount * $rutPercentage;
        });

        return $totalRutAmount;
    }

    private function getTransport(?CustomerDiscount $discount)
    {
        $product = get_transport();

        return [
            'description' => $product->name,
            'fortnox_article_id' => $product->fortnox_article_id,
            'quantity' => 1,
            'unit' => $product->unit,
            'price' => $product->price,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => $product->vat_group,
            'has_rut' => $product->has_rut,
        ];
    }

    private function getMaterial(
        ScheduleCleaning $cleaning,
        ?CustomerDiscount $discount,
    ) {
        $product = get_material();

        return [
            'description' => $product->name,
            'fortnox_article_id' => $product->fortnox_article_id,
            'quantity' => $cleaning->quarters / 4,
            'unit' => $product->unit,
            'price' => $product->price * 4,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => $product->vat_group,
            'has_rut' => $product->has_rut,
        ];
    }

    /**
     * Get service row.
     * Service row quantity is using hours and price is per hour.
     */
    private function getService(ScheduleCleaning $cleaning, ?CustomerDiscount $discount): array
    {
        return [[
            'description' => $cleaning->subscription->service->name,
            'fortnox_article_id' => $cleaning->subscription->service->fortnox_article_id,
            'quantity' => $cleaning->quarters / 4,
            'unit' => ProductUnitEnum::Hours(),
            'price' => $cleaning->subscription->service->price * 4,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => $cleaning->subscription->service->vat_group,
            'has_rut' => $cleaning->subscription->service->has_rut,
        ]];
    }

    private function getName(ScheduleCleaningProduct $product): string
    {
        if ($product->payment_method === CleaningProductPaymentMethodEnum::Credit()) {
            return $product->product->name.' (Krediter användes för att betala)';
        }

        return $product->product->name;
    }
}
