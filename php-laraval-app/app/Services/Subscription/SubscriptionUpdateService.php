<?php

namespace App\Services\Subscription;

use App\DTOs\Subscription\UpdateSubscriptionRequestDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Addon;
use App\Models\Product;
use App\Models\Subscription;

class SubscriptionUpdateService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {
    }

    /**
     * Calculate the total raw price from total price, products and addons.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    public function getTotalRawPrice($request, $subscription): float
    {
        if ($request->isOptional('total_price')) {
            return $subscription->total_raw_price;
        }

        $totalRawPrice = $request->total_price;

        $productIds = $request->products->map(fn ($product) => $product->id);
        $products = Product::whereIn('id', $productIds)->get();

        /** @var Product $product */
        foreach ($products as $product) {
            $totalRawPrice -= $product->price_with_vat;
        }

        $addons = Addon::whereIn('id', $request->addon_ids)->get();

        /** @var Addon $addon */
        foreach ($addons as $addon) {
            $totalRawPrice -= $addon->price_with_vat;
        }

        return $totalRawPrice;
    }

    /**
     * Set fixed price for subscription.
     *
     * @param  UpdateSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     * @param  float  $totalRawPrice
     * @param  bool  $hasRut
     */
    public function setFixedPrice($request, $subscription, $totalRawPrice, $hasRut)
    {
        // update or add fixed price if total price is changed
        if ($request->isNotOptional('total_price') && $totalRawPrice !== $subscription->total_raw_price) {
            $vat = VatNumbersEnum::TwentyFive();
            $price = $request->total_price / (1 + $vat / 100);

            /**
             * If subscription has fixed price and fixed price subscriptions is 1
             * then update the fixed price row / reuse the fixed price
             */
            if ($subscription->fixed_price_id && $subscription->fixedPrice->subscriptions()->count() === 1) {
                $subscription->fixedPrice->rows()->where('type', FixedPriceRowTypeEnum::Service())->update([
                    'price' => $price,
                    'vat_group' => $vat,
                ]);
            } else {
                $fixedPrice = $this->subscriptionService->getFixedPrice(
                    $price,
                    $vat,
                    $subscription->user_id,
                    $hasRut
                );

                $subscription->update([
                    'fixed_price_id' => $fixedPrice->id,
                ]);

                // If fixed price is trashed or soft delete, restore it
                if ($fixedPrice->trashed()) {
                    $fixedPrice->restore();
                }
            }
        }
    }
}
