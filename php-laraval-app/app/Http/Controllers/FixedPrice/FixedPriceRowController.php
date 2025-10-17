<?php

namespace App\Http\Controllers\FixedPrice;

use App\DTOs\FixedPriceRow\CreateFixedPriceRowRequestDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Http\Controllers\Controller;
use App\Jobs\FixedPriceUpdateInvoiceSummationJob;
use App\Models\FixedPrice;
use App\Models\PriceAdjustmentRow;
use App\Services\OrderFixedPriceService;
use App\Services\PriceAdjustmentService;
use DB;
use Illuminate\Http\RedirectResponse;

class FixedPriceRowController extends Controller
{
    /**
     * Create fixed price row.
     */
    public function store(
        FixedPrice $fixedPrice,
        CreateFixedPriceRowRequestDTO $request,
    ): RedirectResponse {
        $rutTypes = [FixedPriceRowTypeEnum::Service()];

        // validate if type not contains in existed fixed price row types
        if ($fixedPrice->rows()->where('type', $request->type)->exists()) {
            return back()->with('error', __('fixed price row type already exists'));
        }

        DB::transaction(function () use ($request, $fixedPrice, $rutTypes) {
            /** @var \App\Models\FixedPriceRow */
            $row = $fixedPrice->rows()->create([
                ...$request->toArray(),
                'price' => $request->price / (1 + $request->vat_group / 100),
                'has_rut' => in_array($request->type, $rutTypes),
            ]);

            if ($request->laundry_product_ids && $request->type === FixedPriceRowTypeEnum::Laundry()) {
                $fixedPrice->laundryProducts()->attach($request->laundry_product_ids);
            }

            OrderFixedPriceService::addRow($fixedPrice, $row);
        });

        FixedPriceUpdateInvoiceSummationJob::dispatchAfterResponse($fixedPrice);

        return back()->with('success', __('fixed price row created successfully'));
    }

    /**
     * Update fixed price row.
     */
    public function update(
        FixedPrice $fixedPrice,
        int $rowId,
        CreateFixedPriceRowRequestDTO $request,
    ): RedirectResponse {
        /** @var \App\Models\FixedPriceRow|null */
        $row = $fixedPrice->rows()->find($rowId);

        if (! $row) {
            return back()->with('error', __('fixed price row not found'));
        }

        if ($row->type !== $request->type) {
            return back()->with('error', __('can not change fixed price row type'));
        }

        $rutTypes = [FixedPriceRowTypeEnum::Service(), FixedPriceRowTypeEnum::Laundry()];

        // validate if type not contains in existed fixed price row types
        if ($fixedPrice->rows()
            ->where('type', $request->type)
            ->whereNot('id', $rowId)->exists()) {
            return back()->with('error', __('fixed price row type already exists'));
        }

        $vat = $request->vat_group ?? $row->vat_group;

        DB::transaction(function () use ($request, $row, $fixedPrice, $vat, $rutTypes) {
            // Update fixed price price adjustment if price or vat changed
            if ($row->price_with_vat !== $request->price || $row->vat_group !== $vat) {
                PriceAdjustmentService::updatePriceAdjustmentRow($fixedPrice, $request->price, $vat);
            }

            // Update row
            $row->update([
                ...$request->toArray(),
                'price' => $request->price / (1 + $vat / 100),
                'has_rut' => in_array($request->type, $rutTypes),
            ]);

            if ($row->type === FixedPriceRowTypeEnum::Laundry()) {
                $fixedPrice->laundryProducts()->sync($request->laundry_product_ids);
            }

            // Update row in order fixed price
            OrderFixedPriceService::updateRow($fixedPrice, $row);
        });

        FixedPriceUpdateInvoiceSummationJob::dispatchAfterResponse($fixedPrice);

        return back()->with('success', __('fixed price row updated successfully'));
    }

    /**
     * Delete fixed price row.
     */
    public function destroy(
        FixedPrice $fixedPrice,
        int $rowId,
    ): RedirectResponse {
        /** @var \App\Models\FixedPriceRow|null */
        $row = $fixedPrice->rows()->find($rowId);

        if (! $row) {
            return back()->with('error', __('fixed price row not found'));
        }

        if (in_array($fixedPrice->type, [FixedPriceTypeEnum::Laundry(), FixedPriceTypeEnum::CleaningAndLaundry()]) &&
            $row->type === FixedPriceRowTypeEnum::Laundry()
        ) {
            return back()->with('error', __('can not delete fixed price row'));
        }

        if (in_array($fixedPrice->type, [FixedPriceTypeEnum::Cleaning(), FixedPriceTypeEnum::CleaningAndLaundry()]) &&
            $row->type === FixedPriceRowTypeEnum::Service()
        ) {
            return back()->with('error', __('can not delete fixed price row'));
        }

        DB::transaction(function () use ($row, $fixedPrice) {
            // Delete price adjustment rows
            PriceAdjustmentRow::fixedPrice($fixedPrice->id)->pending()->delete();

            // Delete row from order fixed price
            OrderFixedPriceService::deleteRow($fixedPrice, $row->type);

            $row->delete();
        });

        FixedPriceUpdateInvoiceSummationJob::dispatchAfterResponse($fixedPrice);

        return back()->with('success', __('fixed price row deleted successfully'));
    }
}
