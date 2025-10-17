<?php

namespace App\Http\Controllers\UnassignSubscription;

use App\DTOs\UnassignSubscription\UpdateUnassignSubscriptionRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\VatNumbersEnum;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UnassignSubscription;
use App\Services\Subscription\SubscriptionCleaningService;
use App\Services\Subscription\SubscriptionLaundryService;
use App\Services\Subscription\SubscriptionNotificationService;
use App\Services\Subscription\SubscriptionService;
use App\Services\Subscription\SubscriptionViewService;
use App\Services\UnassignSubscription\UnassignSubscriptionCleaningService;
use App\Services\UnassignSubscription\UnassignSubscriptionLaundryService;
use DB;

class UnassignSubscriptionActionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionViewService $subscriptionView,
        protected SubscriptionNotificationService $subscriptionNotification,
        protected UnassignSubscriptionCleaningService $unassignCleaningService,
        protected UnassignSubscriptionLaundryService $unassignLaundryService,
        protected SubscriptionCleaningService $cleaningService,
        protected SubscriptionLaundryService $laundryService,
    ) {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function generate(
        UpdateUnassignSubscriptionRequestDTO $request,
        UnassignSubscription $unassignSubscription,
    ) {
        $unassignService = $request->cleaning_detail ? $this->unassignCleaningService : $this->unassignLaundryService;
        $service = $request->cleaning_detail ? $this->cleaningService : $this->laundryService;

        $unassignService->preprocess($request, $unassignSubscription);
        $unassignService->checkCollision($request);

        $products = products_request_to_array($request->product_carts->toArray());

        $subscription = DB::transaction(
            function () use (
                $request,
                $unassignSubscription,
                $products,
                $service,
            ) {
                $subscription = $service->create($request);
                $subscription->addons()->sync($request->addon_ids);
                $subscription->products()->sync($products);

                $this->setFixedPrice($request, $subscription);

                $unassignSubscription->delete();

                return $subscription;
            }
        );

        $service->createInitialSchedules($subscription);
        $this->subscriptionNotification->sendCreated($subscription);

        return back()->with('success', __('subscription created successfully'));
    }

    /**
     *  Set fixed price for subscription.
     *
     * @param  UpdateUnassignSubscriptionRequestDTO  $request
     * @param  Subscription  $subscription
     */
    private function setFixedPrice($request, $subscription)
    {
        if ($request->isNotOptional('total_price') && $request->fixed_price) {
            $vat = VatNumbersEnum::TwentyFive();
            $price = $request->fixed_price / (1 + $vat / 100);
            $fixedPrice = $this->subscriptionService->getFixedPrice(
                $price,
                $vat,
                $request->user_id,
                $request->type === MembershipTypeEnum::Private()
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
