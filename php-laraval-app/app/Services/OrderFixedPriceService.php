<?php

namespace App\Services;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Models\FixedPrice;
use App\Models\FixedPriceRow;
use App\Models\Invoice;
use App\Models\OldOrder;
use App\Models\Order;
use App\Models\OrderFixedPrice;
use App\Models\Schedule;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFixedPriceService
{
    /**
     * Get array of fixed price ids from orders.
     */
    public static function getFixedPriceIds(Invoice $invoice): array
    {
        return $invoice->orders()
            ->where('orderable_type', '!=', OldOrder::class)
            ->with(['subscription' => function (BelongsTo $query) {
                $query->withTrashed()->with(['fixedPrice']);
            }])
            ->get()
            ->pluck('subscription.fixedPrice')
            ->filter(function (?FixedPrice $fixedPrice) use ($invoice) {
                // Filter non-monthly fixed prices
                if (! $fixedPrice || ! $fixedPrice->is_per_order || $fixedPrice->deleted_at) {
                    return false;
                }

                // Exclude the fixed price if it's before the invoice month
                if ($fixedPrice->start_date &&
                    $fixedPrice->start_date->month > $invoice->month &&
                    $fixedPrice->start_date->year >= $invoice->year
                ) {
                    return false;
                }

                // Exclude the fixed price if it's after the invoice month
                if ($fixedPrice->end_date &&
                    $fixedPrice->end_date->month < $invoice->month &&
                    $fixedPrice->end_date->year <= $invoice->year
                ) {
                    return false;
                }

                return true;
            })
            ->pluck('id')
            ->unique()
            ->toArray();
    }

    private static function saveOrderFixedPrice(int $fixedPriceId)
    {
        $fixedPrice = FixedPrice::find($fixedPriceId);
        $orderFixedPrice = OrderFixedPrice::create([]);
        $orderFixedPrice->rows()->createMany($fixedPrice->rows->toArray());

        return $orderFixedPrice;
    }

    /**
     * Get monthly order fixed price.
     *
     * @param  FixedPrice  $fixedPrice
     * @param  int  $month
     * @param  int  $year
     * @return \App\Models\OrderFixedPrice|null
     */
    public static function getMonthlyFixedPrice($fixedPrice, $month, $year)
    {
        // Determine which types are eligible for this fixed price
        $eligibleTypes = [$fixedPrice->type];

        // If the fixed price is not cleaning_and_laundry, it's eligible for cleaning and laundry
        if ($fixedPrice->type !== FixedPriceTypeEnum::CleaningAndLaundry()) {
            $eligibleTypes = [
                $fixedPrice->type,
                FixedPriceTypeEnum::CleaningAndLaundry(),
            ];
        }

        return OrderFixedPrice::where('fixed_price_id', $fixedPrice->id)
            ->whereIn('type', $eligibleTypes)
            ->where('is_per_order', false)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->first();
    }

    /**
     * Create order fixed price from fixed price.
     *
     * @param  FixedPrice  $fixedPrice
     * @param  Order|null  $order
     * @param  bool  $isCredited
     */
    public static function fromFixedPrice(
        $fixedPrice,
        $order = null,
        $isCredited = false,
    ) {
        $rows = $fixedPrice->rows->map(fn ($row) => [
            'type' => $row->type,
            'description' => self::getFixedPriceRowDescription(
                $row,
                $fixedPrice->is_per_order,
                $isCredited,
            ),
            'quantity' => $row->quantity,
            'price' => $row->price,
            'vat_group' => $row->vat_group,
            'has_rut' => $row->has_rut,
        ]);

        $orderFixedPrice = OrderFixedPrice::make([
            'fixed_price_id' => $fixedPrice->id,
            'is_per_order' => $fixedPrice->is_per_order,
            'type' => $fixedPrice->type,
        ]);
        $orderFixedPrice->created_at = $order ? $order->ordered_at : now();
        $orderFixedPrice->save();
        $orderFixedPrice->rows()->createMany($rows);

        return $orderFixedPrice;
    }

    public static function findOrCreate(FixedPrice $fixedPrice, int $month, int $year): OrderFixedPrice
    {
        $orderFixedPrice = self::getMonthlyFixedPrice($fixedPrice, $month, $year);

        if (! $orderFixedPrice) {
            $orderFixedPrice = self::fromFixedPrice($fixedPrice);
        }

        return $orderFixedPrice;
    }

    // Case 1: Order already has order fixed price, fixed price is per order, is per order changed
    // Case 2: Order already has order fixed price, fixed price is per order, is per order not changed
    // Case 3: Order already has order fixed price, fixed price is monthly, is per order changed
    // Case 4: Order already has order fixed price, fixed price is monthly, is per order not changed
    // Case 5: Order does not have order fixed price, fixed price is per order, is per order changed
    // Case 6: Order does not have order fixed price, fixed price is per order, is per order not changed
    // Case 7: Order does not have order fixed price, fixed price is monthly, is per order changed
    // Case 8: Order does not have order fixed price, fixed price is monthly, is per order not changed
    public function applyToSubscriptionOrders(
        FixedPrice $fixedPrice,
        bool $isPerOrderChanged,
        InvoiceSummationService $invoiceSummationService,
    ) {
        DB::transaction(function () use ($fixedPrice, $isPerOrderChanged, $invoiceSummationService) {
            $orderFixedPriceIds = [];
            $invoiceIds = [];

            // Need to loop because every order has different case
            /** @var \App\Models\Subscription */
            foreach ($fixedPrice->subscriptions()->withTrashed()->get() as $subscription) {
                $orders = $subscription->draftOrders()->with('fixedPrice.rows')->get();

                /** @var \App\Models\Order $order */
                foreach ($orders as $order) {
                    if (! FixedPriceService::isApplicable($fixedPrice, $order->ordered_at)) {
                        // TODO: handle case when change start_date or end_date fixed price
                        continue;
                    }

                    $invoiceIds[] = $order->invoice_id;
                    $isCredited = $order->orderable_type === Schedule::class &&
                        $order->orderable->status === ScheduleStatusEnum::Cancel();

                    // Cover case 7,8
                    if (! $order->fixedPrice && ! $fixedPrice->is_per_order) {
                        // If fixed price is monthly, check if it can be reused.
                        // Check if order fixed price for the same month and year exists.
                        $orderFixedPrice = OrderFixedPriceService::getMonthlyFixedPrice(
                            $fixedPrice,
                            $order->ordered_at->month,
                            $order->ordered_at->year,
                        );

                        if ($orderFixedPrice) {
                            $order->update(['order_fixed_price_id' => $orderFixedPrice->id]);

                            continue;
                        }
                    } elseif ($order->fixedPrice && $fixedPrice->is_per_order
                        && $isPerOrderChanged && $order->fixedPrice->orders()->count() === 1) {
                        // Delete order fixed price. It doesn't use anymore
                        // Cover case 1
                        $order->fixedPrice->rows()->delete();

                        // Change fixed price to per order
                        $order->fixedPrice->update(['is_per_order' => true]);
                        $this->createNewRows($fixedPrice, $order, $isCredited);

                        continue;
                    } elseif ($order->fixedPrice && $fixedPrice->is_per_order && ! $isPerOrderChanged) {
                        // Cover case 2
                        $this->createNewRows($fixedPrice, $order, $isCredited);

                        continue;
                    } elseif ($order->fixedPrice && ! $fixedPrice->is_per_order && $isPerOrderChanged) {
                        // Cover case 3
                        $result = $this->perOrderToMonthly($fixedPrice, $order);

                        // If order fixed price exists.
                        if ($result) {
                            continue;
                        }
                    } elseif ($order->fixedPrice && ! $fixedPrice->is_per_order && ! $isPerOrderChanged) {
                        // Cover case 4
                        if (in_array($order->fixedPrice->id, $orderFixedPriceIds)) {
                            continue;
                        }

                        $orderFixedPriceIds[] = $order->fixedPrice->id;
                        $this->createNewRows($fixedPrice, $order, $isCredited);

                        continue;
                    }

                    // Case 1,5,6
                    $orderFixedPrice = OrderFixedPriceService::fromFixedPrice($fixedPrice, $order, $isCredited);
                    $order->update(['order_fixed_price_id' => $orderFixedPrice->id]);
                }
            }

            /** @var \Illuminate\Database\Eloquent\Collection<int,Invoice> */
            $invoices = Invoice::with(InvoiceSummationService::REQUIRED_FIELDS)
                ->whereIn('id', $invoiceIds)
                ->get();

            foreach ($invoices as $invoice) {
                $summation = $invoiceSummationService->getSummation($invoice);
                $invoice->update($summation);
            }
        });
    }

    /**
     * Delete old rows and create new rows.
     * Change fixed price to monthly.
     */
    private function perOrderToMonthly(FixedPrice $fixedPrice, Order $order): ?OrderFixedPrice
    {
        $order->fixedPrice->forceDelete();

        // If fixed price is monthly, check if it can be reused.
        // Check if order fixed price for the same month and year exists.
        $orderFixedPrice = OrderFixedPriceService::getMonthlyFixedPrice(
            $fixedPrice,
            $order->ordered_at->month,
            $order->ordered_at->year,
        );

        if ($orderFixedPrice) {
            $order->update(['order_fixed_price_id' => $orderFixedPrice->id]);
        }

        return $orderFixedPrice;
    }

    /**
     * Delete old rows and create new rows.
     */
    private static function createNewRows(FixedPrice $fixedPrice, Order $order, bool $isCredited = false)
    {
        $order->fixedPrice->rows()->delete();

        $rows = $fixedPrice->rows->map(fn ($row) => [
            'type' => $row->type,
            'description' => self::getFixedPriceRowDescription(
                $row,
                $fixedPrice->is_per_order,
                $isCredited,
            ),
            'quantity' => $row->quantity,
            'price' => $row->price,
            'vat_group' => $row->vat_group,
            'has_rut' => $row->has_rut,
        ]);

        $order->fixedPrice->rows()->createMany($rows);
    }

    /**
     * Add new order fixed price row based on fixed price row.
     *
     * @param  FixedPrice  $fixedPrice
     * @param  FixedPriceRow  $row
     */
    public static function addRow($fixedPrice, $row)
    {
        $orderFixedPrices = $fixedPrice->orderFixedPrices()
            ->whereHas('orders', function ($query) {
                $query->where('status', OrderStatusEnum::Draft());
            })
            ->get();

        /** @var \App\Models\OrderFixedPrice */
        foreach ($orderFixedPrices as $orderFixedPrice) {
            $orderFixedPrice->rows()->create([
                'type' => $row->type,
                'quantity' => $row->quantity,
                'price' => $row->price,
                'vat_group' => $row->vat_group,
                'has_rut' => $row->has_rut,
            ]);
        }
    }

    /**
     * Update order fixed price row based on fixed price row.
     *
     * @param  FixedPrice  $fixedPrice
     * @param  FixedPriceRow  $row
     */
    public static function updateRow($fixedPrice, $row)
    {
        $orderFixedPrices = $fixedPrice->orderFixedPrices()
            ->whereHas('orders', function ($query) {
                $query->where('status', OrderStatusEnum::Draft());
            })
            ->get();

        /** @var \App\Models\OrderFixedPrice */
        foreach ($orderFixedPrices as $orderFixedPrice) {
            $orderFixedPrice->rows()
                ->where('type', $row->type)
                ->update([
                    'type' => $row->type,
                    'description' => self::getFixedPriceRowDescription(
                        $row,
                        $fixedPrice->is_per_order,
                        false,
                    ),
                    'quantity' => $row->quantity,
                    'price' => $row->price,
                    'vat_group' => $row->vat_group,
                    'has_rut' => $row->has_rut,
                ]);
        }
    }

    /**
     * Delete order fixed price row based on fixed price.
     *
     * @param  FixedPrice  $fixedPrice
     * @param  string  $type
     */
    public static function deleteRow($fixedPrice, $type)
    {
        $orderFixedPrices = $fixedPrice->orderFixedPrices()
            ->whereHas('orders', function ($query) {
                $query->where('status', OrderStatusEnum::Draft());
            })
            ->get();

        /** @var \App\Models\OrderFixedPrice */
        foreach ($orderFixedPrices as $orderFixedPrice) {
            $orderFixedPrice->rows()->where('type', $type)->delete();
        }
    }

    /**
     * Get the description for the fixed price row.
     */
    public static function getFixedPriceRowDescription(
        FixedPriceRow $row,
        bool $isPerOrder = false,
        bool $isCredited = false,
    ) {
        if ($row->type === FixedPriceRowTypeEnum::Service() && $isPerOrder && $isCredited) {
            return __('cleaning service fixed price').get_credit_refund_description();
        } elseif ($row->type === FixedPriceRowTypeEnum::Service()) {
            return __('cleaning service fixed price');
        } elseif ($row->type === FixedPriceRowTypeEnum::Laundry()) {
            return __($row->type).' '.__('fixed price').' '.get_laundry_row_description($row);
        }

        return null;
    }
}
