<?php

namespace App\Services;

use App\Enums\Invoice\InvoiceTypeEnum;
use App\Models\Invoice;

class InvoiceSummationService
{
    public const REQUIRED_FIELDS = [
        'monthlyFixedPrices.rows:id,order_fixed_price_id,price,vat_group,quantity,has_rut',
        'orders:id,invoice_id,subscription_id,service_id,order_fixed_price_id,orderable_type',
        'orders.fixedPrice:id,is_per_order',
        'orders.fixedPrice.rows:id,order_fixed_price_id,price,vat_group,quantity,has_rut',
        'orders.subscription.products.product:id,fortnox_article_id',
        'orders.service:id,fortnox_article_id',
        'orders.rows:id,order_id,fortnox_article_id,price,vat,quantity,discount_percentage,has_rut',
    ];

    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * Calculate single invoice summation
     */
    public function getSummation(Invoice $invoice): array
    {
        $totalGross = 0.0;
        $totalNet = 0.0;
        $totalVat = 0.0;
        $totalRut = 0.0;

        foreach ($invoice->monthlyFixedPrices as $monthlyFixedPrice) {
            $totalFixedPriceRut = $monthlyFixedPrice->total_rut_amount;

            $totalGross += $monthlyFixedPrice->total_gross_amount;
            $totalNet += $monthlyFixedPrice->total_net_amount;
            $totalVat += $monthlyFixedPrice->total_vat_amount;
            $totalRut += $totalFixedPriceRut;
        }

        foreach ($invoice->orders as $order) {
            $fixedPriceArticleIds = get_fixed_price_article_ids($order);
            $orderTotalRut = $this->orderService->getTotalRutAmount($order, $invoice->type, $fixedPriceArticleIds);

            $totalGross += $this->orderService->getTotalGrossAmount($order, $fixedPriceArticleIds);
            $totalNet += $this->orderService->getTotalNetAmount($order, $fixedPriceArticleIds);
            $totalVat += $this->orderService->getTotalVatAmount($order, $fixedPriceArticleIds);
            $totalRut += $orderTotalRut;
        }

        return [
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'total_vat' => $totalVat,
            // if invoice type is laundry, we don't need to floor the total rut
            'total_rut' => $invoice->type === InvoiceTypeEnum::Laundry() ? $totalRut : floor($totalRut),
        ];
    }
}
