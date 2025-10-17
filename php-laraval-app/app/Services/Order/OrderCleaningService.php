<?php

namespace App\Services\Order;

use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\Schedule\ScheduleItemPaymentMethodEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Addon;
use App\Models\CustomerDiscount;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Services\DiscountService;
use App\Services\FixedPriceService;
use App\Services\OrderFixedPriceService;

class OrderCleaningService
{
    /**
     * Create order based on schedule cleaning.
     *
     * @param  Schedule  $schedule
     * @param  bool  $isCredited
     * @return array{0:Order,1:Invoice}
     */
    public function createOrder($schedule, $isCredited = false)
    {
        /** @var \App\Models\Subscription */
        $subscription = $schedule->subscription;
        $type = Invoice::getUserType(
            $schedule->user_id,
            $schedule->customer->membership_type,
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
            'subscription_id' => $schedule->subscription_id,
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
     * @param  Order  $order
     * @param  Schedule  $schedule
     * @param  \Illuminate\Database\Eloquent\Collection<array-key, ScheduleItem>|null  $items
     */
    public function createOrderRows($order, $schedule, $items = null)
    {
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $schedule->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );
        $rows = $this->getService($schedule, $discount);
        $items = $items ?? $schedule->items;

        foreach ($items as $item) {
            // Skip if item is laundry addon
            // The product will include in the order instead of the addon
            if ($item->itemable_type === Addon::class &&
                $item->itemable_id === config('downstairs.addons.laundry.id')
            ) {
                continue;
            }

            $rows[] = [
                'description' => $this->getName($item),
                'fortnox_article_id' => $item->itemable->fortnox_article_id,
                'quantity' => 1,
                'unit' => $item->itemable->unit,
                'price' => $item->price,
                'discount_percentage' => max($item->discount_percentage, $discount ? $discount->value : 0),
                'vat' => $item->itemable->vat_group,
                'has_rut' => $item->itemable->has_rut,
            ];
        }

        $rows[] = $this->getTransport($discount);
        $rows[] = $this->getMaterial($schedule, $discount);

        $order->rows()->createMany($rows);
        DiscountService::useDiscount($discount);
    }

    /**
     * Create order row when cancel by customer (refund).
     *
     * @param  Order  $order
     * @param  Schedule  $schedule
     */
    public function cancelByCustomer($order, $schedule)
    {
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
     * @param  Order  $order
     * @param  Schedule  $schedule
     */
    public function cancelByAdmin($order, $schedule)
    {
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
     *
     * @param  Order  $order
     * @param  FixedPrice|null  $fixedPrice
     * @param  bool  $isCredited
     */
    public function applyFixedPrice($order, $fixedPrice, $isCredited = false)
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
        Schedule $schedule,
        ?CustomerDiscount $discount,
    ) {
        $product = get_material();

        return [
            'description' => $product->name,
            'fortnox_article_id' => $product->fortnox_article_id,
            'quantity' => $schedule->quarters / 4,
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
     *
     * @param  Schedule  $schedule
     * @param  CustomerDiscount|null  $discount
     * @return array
     */
    private function getService($schedule, $discount)
    {
        return [[
            'description' => $schedule->service->name,
            'fortnox_article_id' => $schedule->service->fortnox_article_id,
            'quantity' => $schedule->quarters / 4,
            'unit' => ProductUnitEnum::Hours(),
            'price' => $schedule->service->price * 4,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => $schedule->service->vat_group,
            'has_rut' => $schedule->service->has_rut,
        ]];
    }

    private function getName(ScheduleItem $item): string
    {
        if ($item->payment_method === ScheduleItemPaymentMethodEnum::Credit()) {
            return $item->itemable->name.' (Krediter användes för att betala)';
        }

        return $item->itemable->name;
    }
}
