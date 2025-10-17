<?php

namespace App\Http\Controllers\Order;

use App\DTOs\OrderRow\CreateOrderRowRequestDTO;
use App\DTOs\OrderRow\UpdateOrderRowRequestDTO;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Order;
use App\Models\OrderRow;
use App\Services\InvoiceSummationService;
use DB;
use Illuminate\Http\RedirectResponse;

class OrderRowController extends Controller
{
    public function __construct(
        private readonly InvoiceSummationService $invoiceSummationService,
    ) {
    }

    /**
     * Create order row.
     */
    public function store(
        Order $order,
        CreateOrderRowRequestDTO $request,
    ): RedirectResponse {
        $order->rows()->create([
            ...$request->toArray(),
            'price' => $request->price / (1 + $request->vat / 100),
        ]);

        // only update invoice summation if price and quantity are greater than 0
        if ($request->price > 0 && $request->quantity > 0) {
            UpdateInvoiceSummationJob::dispatchAfterResponse($order->invoice);
        }

        return back()->with('success', __('order row created successfully'));
    }

    /**
     * Update order row.
     */
    public function update(
        Order $order,
        int $rowId,
        UpdateOrderRowRequestDTO $request,
    ): RedirectResponse {
        /** @var OrderRow $row */
        $row = $order->rows()->find($rowId);

        if (! $row) {
            return back()->with('error', __('order row not found'));
        }

        // update price and vat
        $vat = $request->isNotOptional('vat') ? $request->vat : $row->vat;
        $price = $request->isNotOptional('price') ?
            $request->price / (1 + $vat / 100) : $row->price;
        $quantity = $request->isNotOptional('quantity') ? $request->quantity : $row->quantity;
        $discountPercentage = $request->isNotOptional('discount_percentage') ?
            $request->discount_percentage : $row->discount_percentage;
        $hasRut = $request->isNotOptional('has_rut') ? $request->has_rut : $row->has_rut;
        $shouldUpdateInvoiceSummation = $price !== $row->price ||
            $quantity !== $row->quantity || $vat !== $row->vat ||
            $discountPercentage !== $row->discount_percentage || $hasRut !== $row->has_rut;

        DB::transaction(function () use ($row, $request, $price, $vat, $order) {
            $row->update([
                ...$request->toArray(),
                'price' => $price,
                'vat' => $vat,
            ]);

            // sync quantity service row and material row
            if ($row->is_service_row) {
                $material = get_material();

                $order->rows()
                    ->where('fortnox_article_id', $material->fortnox_article_id)
                    ->update([
                        'quantity' => $row->quantity,
                    ]);
            } elseif ($row->is_material_row) {
                $order->rows()
                    ->where('fortnox_article_id', $order->service->fortnox_article_id)
                    ->update([
                        'quantity' => $row->quantity,
                    ]);
            }

            $summation = $this->invoiceSummationService->getSummation($order->invoice);
            $order->invoice->update($summation);
        });

        if ($shouldUpdateInvoiceSummation) {
            UpdateInvoiceSummationJob::dispatchAfterResponse($order->invoice);
        }

        return back()->with('success', __('order row updated successfully'));
    }

    /**
     * Delete order row.
     */
    public function destroy(
        Order $order,
        int $rowId,
    ): RedirectResponse {
        $row = $order->rows()->find($rowId);

        if (! $row) {
            return back()->with('error', __('order row not found'));
        }

        $row->delete();

        UpdateInvoiceSummationJob::dispatchAfterResponse($order->invoice);

        return back()->with('success', __('order row deleted successfully'));
    }
}
