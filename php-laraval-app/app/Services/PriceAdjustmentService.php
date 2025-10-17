<?php

namespace App\Services;

use App\Enums\RutNumbersEnum;
use App\Models\Addon;
use App\Models\FixedPrice;
use App\Models\PriceAdjustmentRow;
use App\Models\Product;
use App\Models\Service;

class PriceAdjustmentService
{
    /**
     * Get the model based on the type.
     */
    public static function getModel(string $type): ?string
    {
        return match ($type) {
            'service' => Service::class,
            'addon' => Addon::class,
            'product' => Product::class,
            'fixed_price' => FixedPrice::class,
            default => null,
        };
    }

    /**
     * Get the previous price with VAT based on the model.
     *
     * @param  Service|Addon|Product|FixedPrice  $model
     */
    public static function getPreviousPriceWithVat($model): float
    {
        if ($model instanceof FixedPrice) {
            // sum all rows price with vat, because fixed price can have multiple rows
            $priceWithVat = $model->rows->sum(fn ($row) => $row->price_with_vat);

            return $priceWithVat;
        }

        return $model->price_with_vat;
    }

    /**
     * Get the vat group based on the model.
     *
     * @param  Service|Addon|Product|FixedPrice  $model
     */
    public static function getVat($model): int
    {
        if ($model instanceof FixedPrice) {
            // For fixed price, just only for record, not for calculation
            // It will calculate in the scheduler based on the each row vat group
            return RutNumbersEnum::TwentyFive();
        }

        return $model->vat_group;
    }

    /**
     * Calculate base price based on
     * given price with VAT and adjustment price type.
     *
     * @param  string  $type Price adjustment type
     * @param  float  $adjustablePriceWithVat Price with VAT of the adjustable
     * @param  float  $priceWithVat Price with VAT of the adjustment
     * @param  int  $vatGroup VAT group of the adjustment
     * @param  Service|Addon|Product|FixedPrice  $model
     */
    public static function calculateNewBasePrice(
        $type,
        $adjustablePriceWithVat,
        $priceWithVat,
        $vatGroup,
        $model,
    ): float {
        $result = $priceWithVat;

        switch ($type) {
            case 'dynamic_fixed_with_vat':
                $result = $adjustablePriceWithVat + $priceWithVat;
                break;
            case 'dynamic_percentage':
                $result = $adjustablePriceWithVat + ($adjustablePriceWithVat * $priceWithVat / 100);
                break;
        }

        if ($model instanceof FixedPrice) {
            // For fixed price, just only for record, not for calculation
            // This is need price with vat, because use in attribute
            return $result;
        }

        return price_without_vat($result, $vatGroup);
    }

    /**
     * Update price adjustment row based on the new price and vat on the model.
     *
     * @param  Service|Addon|Product|FixedPrice  $model
     */
    public static function updatePriceAdjustmentRow($model, float $priceWithVat, float $vat): void
    {
        $type = get_class($model);
        $rows = PriceAdjustmentRow::where('adjustable_id', $model->id)
            ->where('adjustable_type', $type)
            ->pending()
            ->get();

        $rows->each(function ($row) use ($priceWithVat, $vat, $model) {
            $row->update([
                'price' => PriceAdjustmentService::calculateNewBasePrice(
                    $row->priceAdjustment->price_type,
                    $priceWithVat,
                    $row->priceAdjustment->price,
                    $vat,
                    $model,
                ),
                'vat_group' => $vat,
            ]);
        });
    }
}
