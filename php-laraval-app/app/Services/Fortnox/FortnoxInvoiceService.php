<?php

namespace App\Services\Fortnox;

use App\DTOs\Fortnox\Invoice\CreateInvoiceRequestDTO;
use App\DTOs\Fortnox\Invoice\CreateInvoiceRowDTO;
use App\DTOs\Fortnox\Invoice\UpdateInvoiceRequestDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\Invoice\InvoiceCategoryEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Jobs\CreateTaxReductionJob;
use App\Jobs\SetPaidCashInvoiceJob;
use App\Jobs\UpdateTaxReductionJob;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderFixedPrice;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FortnoxInvoiceService
{
    public function create(Invoice $invoice, FortnoxCustomerService $fortnoxService)
    {
        $data = $this->getInvoiceData($invoice);
        $response = $fortnoxService->createInvoice($data);

        if (! $response) {
            return;
        }

        $invoice->update([
            'fortnox_invoice_id' => $response->document_number,
            'status' => InvoiceStatusEnum::Created(),
        ]);

        $invoice->orders()->update([
            'status' => OrderStatusEnum::Progress(),
        ]);

        if ($invoice->customer->membership_type === MembershipTypeEnum::Private() &&
            $data->tax_reduction_type === 'rut') {
            // Set tax reduction for private customer & not cash invoice / not paid invoice
            try {
                CreateTaxReductionJob::dispatchSync(
                    $invoice,
                    $response,
                );
            } catch (\Exception $e) {
                logger()->error('Failed to create tax reduction', [
                    'invoice_id' => $invoice->id,
                    'fortnox_invoice_id' => $response->document_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Set as paid for cash invoice
        if ($invoice->category === InvoiceCategoryEnum::CashInvoice()) {
            try {
                SetPaidCashInvoiceJob::dispatchSync(
                    $invoice,
                    $response,
                );
            } catch (\Exception $e) {
                logger()->error('Failed to set paid cash invoice', [
                    'invoice_id' => $invoice->id,
                    'fortnox_invoice_id' => $response->document_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function update(Invoice $invoice, FortnoxCustomerService $fortnoxService)
    {
        $data = $this->getInvoiceData($invoice);
        $payload = UpdateInvoiceRequestDTO::from($data);

        $response = $fortnoxService->updateInvoice(
            $invoice->fortnox_invoice_id,
            $payload
        );

        if (! $response) {
            return;
        }

        $invoice->orders()->update([
            'status' => OrderStatusEnum::Progress(),
        ]);

        if ($invoice->customer->membership_type === MembershipTypeEnum::Private()) {
            // Set tax reduction for private customer
            try {
                UpdateTaxReductionJob::dispatchSync(
                    $invoice,
                    $response,
                );
            } catch (\Exception $e) {
                logger()->error('Failed to update tax reduction', [
                    'invoice_id' => $invoice->id,
                    'fortnox_invoice_id' => $response->document_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get the data of the invoice that will be sent to Fortnox.
     */
    private function getInvoiceData(Invoice $invoice)
    {
        $invoiceRows = $this->getInvoiceRows($invoice);

        $data = [
            'customer_number' => $invoice->customer->fortnox_id,
            'invoice_date' => $invoice->sent_at->format('Y-m-d'),
            'due_date' => $invoice->due_at->format('Y-m-d'),
            'tax_reduction_type' => $this->getTaxReductionType($invoice),
            'invoice_rows' => $invoiceRows,
            'terms_of_payment' => $invoice->customer->due_days,
            'remarks' => $invoice->remark ?? '',
        ];

        if ($invoice->customer->address->address_2) {
            $data['address2'] = $invoice->customer->address->address_2;
        }

        if ($invoice->customer->reference) {
            $data['your_reference'] = $invoice->customer->reference;
        }

        return CreateInvoiceRequestDTO::from($data);
    }

    /**
     * Get the rows of the invoice.
     */
    private function getInvoiceRows(Invoice $invoice)
    {
        if ($invoice->category === InvoiceCategoryEnum::CashInvoice()) {
            return $this->getCashInvoiceRows($invoice);
        }

        if ($invoice->type === InvoiceTypeEnum::Laundry()) {
            return $this->getLaundryInvoiceRows($invoice);
        }

        return $this->getCleaningInvoiceRows($invoice);
    }

    /**
     * Get the rows of the cash invoice.
     * Cash invoice doesn't have fixed price, so we only need to return the order rows.
     */
    private function getCashInvoiceRows(Invoice $invoice)
    {
        $rows = [];

        // Eager load the order rows
        // Order rows is also used to get tax reduction type
        // So to prevent extra queries, we load the rows here
        $invoice->orders->load('service', 'rows');

        $orders = $invoice->orders->filter(function (Order $order) {
            return $order->rows->isNotEmpty() &&
            in_array($order->status, [OrderStatusEnum::Draft(), OrderStatusEnum::Progress()]);
        });
        $totalOrders = $orders->count();

        foreach ($orders as $index => $order) {
            $orderRows = $this->getOrderRows($order, 'TEXTILECLOTHING');

            $headerRows = $this->getOrderHeaderRows($order);
            $rows = array_merge($rows, $headerRows, $orderRows);

            if ($index < $totalOrders - 1) {
                // Show a separator row between orders
                $rows[] = $this->getSeparatorRow();
            }
        }

        return CreateInvoiceRowDTO::collection($rows);
    }

    /**
     * Get the rows of the laundry invoice.
     */
    private function getLaundryInvoiceRows(Invoice $invoice)
    {
        $rows = $this->getMonthlyFixedPriceRows($invoice);

        // Eager load the order rows
        // Order rows is also used to get tax reduction type
        // So to prevent extra queries, we load the rows here
        $invoice->orders->load(
            'subscription.items.itemable',
            'service',
            'fixedPrice.fixedPrice',
            'rows'
        );

        $orders = $invoice->orders->filter(function (Order $order) {
            return $order->rows->isNotEmpty() &&
            in_array($order->status, [OrderStatusEnum::Draft(), OrderStatusEnum::Progress()]);
        });
        $totalOrders = $orders->count();
        $needFixedPriceSeparator = ! empty($rows);

        foreach ($orders as $index => $order) {
            $orderRows = $this->getOrderRows($order, 'TEXTILECLOTHING');

            if ($needFixedPriceSeparator) {
                // Show a separator row between fixed price and order rows
                $rows[] = $this->getSeparatorRow();
                $needFixedPriceSeparator = false;
            }

            $headerRows = $this->getOrderHeaderRows($order);
            $rows = array_merge($rows, $headerRows, $orderRows);

            if ($index < $totalOrders - 1) {
                // Show a separator row between orders
                $rows[] = $this->getSeparatorRow();
            }
        }

        return CreateInvoiceRowDTO::collection($rows);
    }

    /**
     * Get the rows of the cleaning & laundry or only cleaning invoice.
     */
    private function getCleaningInvoiceRows(Invoice $invoice)
    {
        $rows = $this->getMonthlyFixedPriceRows($invoice);

        // Eager load the order rows
        // Order rows is also used to get tax reduction type
        // So to prevent extra queries, we load the rows here
        $invoice->orders->load(
            'subscription.items.itemable',
            'service',
            'fixedPrice.fixedPrice',
            'rows'
        );

        $orders = $invoice->orders->filter(function (Order $order) {
            return $order->rows->isNotEmpty() &&
            in_array($order->status, [OrderStatusEnum::Draft(), OrderStatusEnum::Progress()]);
        });
        $totalOrders = $orders->count();
        $needFixedPriceSeparator = ! empty($rows);

        foreach ($orders as $index => $order) {
            $orderRows = $this->getOrderRows($order, 'CLEANING');

            if ($needFixedPriceSeparator) {
                // Show a separator row between fixed price and order rows
                $rows[] = $this->getSeparatorRow();
                $needFixedPriceSeparator = false;
            }

            $headerRows = $this->getOrderHeaderRows($order);
            $rows = array_merge($rows, $headerRows, $orderRows);

            if ($index < $totalOrders - 1) {
                // Show a separator row between orders
                $rows[] = $this->getSeparatorRow();
            }
        }

        return CreateInvoiceRowDTO::collection($rows);
    }

    /**
     * Get the rows of the order.
     *
     * @param  string  $houseWorkType the type of house work, can be 'TEXTILECLOTHING' or 'CLEANING'
     */
    private function getOrderRows(Order $order, string $houseWorkType)
    {
        $category = $order->invoice->category;

        // Cash invoice doesn't have fixed price, so we only need to return the fixed price rows.
        $rows = $category !== InvoiceCategoryEnum::CashInvoice() ?
            $this->getPerOrderFixedPriceRows($order) :
            [];

        // Get the article ids that are covered by the fixed price
        $fixedPriceCoveredArticleIds = $order->fixedPrice && $category !== InvoiceCategoryEnum::CashInvoice() ?
            get_fixed_price_article_ids($order) :
            [];

        foreach ($order->rows as $row) {
            // Check if the row is covered by the fixed price
            if ($row->fortnox_article_id &&
                in_array($row->fortnox_article_id, $fixedPriceCoveredArticleIds)
            ) {
                continue;
            }

            $rows[] = [
                'description' => $row->description ?? '',
                'article_number' => $row->fortnox_article_id,
                'delivered_quantity' => "{$row->quantity}",
                'unit' => $row->unit ?? '',
                'price' => $row->price,
                'discount' => $row->discount_percentage,
                'discount_type' => 'PERCENT',
                'house_work' => $row->has_rut,
                'house_work_type' => $row->has_rut ? $houseWorkType : 'EMPTYHOUSEWORK',
                'VAT' => $row->vat,
            ];
        }

        return $rows;
    }

    /**
     * Get monthly fixed price rows.
     */
    private function getMonthlyFixedPriceRows(Invoice $invoice)
    {
        $rows = [];

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,OrderFixedPrice> */
        $monthlyFixedPrices = $invoice->monthlyFixedPrices()
            ->with(['rows', 'fixedPrice' => function (BelongsTo $query) {
                $query->withTrashed();
            }])
            ->get();

        foreach ($monthlyFixedPrices as $monthlyFixedPrice) {
            foreach ($monthlyFixedPrice->rows as $row) {
                $description = $row->description ?? __($row->type).' '.__('fixed price');

                if ($row->type === FixedPriceRowTypeEnum::Laundry()) {
                    $description = $description.' '.get_laundry_row_description($row);
                }

                $rows[] = [
                    'description' => $description,
                    'delivered_quantity' => "{$row->quantity}",
                    'unit' => ProductUnitEnum::Piece(),
                    'price' => $row->price,
                    'discount_type' => 'PERCENT',
                    'house_work' => $row->has_rut,
                    'house_work_type' => $row->has_rut ? 'CLEANING' : 'EMPTYHOUSEWORK',
                    'VAT' => $row->vat_group,
                ];
            }
        }

        return $rows;
    }

    /**
     * Get per order fixed price rows.
     */
    private function getPerOrderFixedPriceRows(Order $order)
    {
        if (! $order->fixedPrice || ! $order->fixedPrice->is_per_order) {
            return [];
        }

        $rows = [];

        foreach ($order->fixedPrice->rows as $row) {
            $description = __($row->type).' '.__('fixed price');

            if ($row->type === FixedPriceRowTypeEnum::Laundry()) {
                $description = $description.' '.get_laundry_row_description($row);
            }

            $rows[] = [
                'description' => $row->description ?? $description,
                'delivered_quantity' => "{$row->quantity}",
                'unit' => ProductUnitEnum::Piece(),
                'price' => $row->price,
                'discount_type' => 'PERCENT',
                'house_work' => $row->has_rut,
                'house_work_type' => $row->has_rut ? 'CLEANING' : 'EMPTYHOUSEWORK',
                'VAT' => $row->vat_group,
            ];
        }

        return $rows;
    }

    /**
     * Get the header row of the order.
     */
    private function getOrderHeaderRows(Order $order): array
    {
        $rows = [];
        $orderDate = $order->ordered_at->format('Y-m-d');
        $orderId = $order->id;

        $rows[] = [
            'description' => "Beställning $orderDate (#$orderId)",
            'discount_type' => 'PERCENT',
        ];

        if ($order->orderable_type === Schedule::class && ! $order->order_fixed_price_id) {
            $rows[] = $this->getSchedulePropertyRow($order);
        }

        return $rows;
    }

    /**
     * Get schedule property row.
     */
    private function getSchedulePropertyRow(Order $order)
    {
        $orderable = $order->orderable;

        /** @var \App\Models\Schedule $orderable */
        $property = $orderable->property->address->address;
        $propertyId = $orderable->property->id;

        return [
            'description' => "Fastighet (#$propertyId): $property",
            'discount_type' => 'PERCENT',
        ];
    }

    /**
     * Get the separator row.
     */
    private function getSeparatorRow()
    {
        return [
            'description' => '----------------------------------------',
            'discount_type' => 'PERCENT',
        ];
    }

    /**
     * Get the type of tax reduction for the invoice.
     */
    private function getTaxReductionType(Invoice $invoice)
    {
        if ($invoice->customer->membership_type !== MembershipTypeEnum::Private()) {
            return 'none';
        }

        $hasRut = $invoice->orders->contains(
            fn (Order $order) => $order->rows->contains('has_rut', true)
        );

        return $hasRut ? 'rut' : 'none';
    }
}
