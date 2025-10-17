<?php

namespace App\Http\Controllers\CompanySubscription;

use App\DTOs\Subscription\CompanySubscriptionWizardRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\VatNumbersEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use App\Services\Subscription\SubscriptionCleaningService;
use App\Services\Subscription\SubscriptionLaundryService;
use App\Services\Subscription\SubscriptionNotificationService;
use App\Services\Subscription\SubscriptionService;
use App\Services\Subscription\SubscriptionViewService;
use DB;
use Inertia\Inertia;
use Inertia\Response;

class CompanySubscriptionWizardController extends BaseUserController
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionViewService $subscriptionView,
        protected SubscriptionNotificationService $subscriptionNotification,
        protected SubscriptionCleaningService $cleaningService,
        protected SubscriptionLaundryService $laundryService,
    ) {
    }

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $transport = get_transport();
        $material = get_material();

        return Inertia::render('CompanySubscription/Wizard/index', [
            'users' => $this->subscriptionView->getUsers(MembershipTypeEnum::Company()),
            'frequencies' => $this->subscriptionView->getFrequencies(),
            'teams' => $this->subscriptionView->getTeams(),
            'services' => $this->subscriptionView->getServices(MembershipTypeEnum::Company()),
            'transportPrice' => $transport ? $transport->price_with_vat : 0,
            'materialPrice' => $material ? $material->price_with_vat : 0,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanySubscriptionWizardRequestDTO $request)
    {
        $service = $request->cleaning_detail ? $this->cleaningService : $this->laundryService;

        $service->preprocess($request);
        $service->checkCollision($request, null);

        $products = products_request_to_array($request->products->toArray());

        $subscription = DB::transaction(
            function () use (
                $request,
                $products,
                $service,
            ) {
                $subscription = $service->create($request);
                $subscription->addons()->sync($request->addon_ids);
                $subscription->products()->sync($products);
                $this->setFixedPrice($request, $subscription);

                return $subscription;
            }
        );

        $service->createInitialSchedules($subscription);
        $this->sendWelcomeNotification($request->user_id);
        $this->subscriptionNotification->sendCreated($subscription);

        return back()->with('success', __('subscription created successfully'));
    }

    private function sendWelcomeNotification($userId): void
    {
        $user = User::find($userId);

        if ($user->subscriptions()->withTrashed()->count() === 1) {
            scoped_localize($user->info->language, function () use ($user) {
                $user->notify(new WelcomeCustomerNotification());
            });
        }
    }

    /**
     *  Set fixed price for subscription.
     *
     * @param  SubscriptionWizardRequestDTO  $request
     * @param  Subscription  $subscription
     */
    private function setFixedPrice($request, $subscription)
    {
        if ($request->isNotOptional('total_price')) {
            $vat = VatNumbersEnum::TwentyFive();
            $price = $request->total_price / (1 + $vat / 100);
            $fixedPrice = $this->subscriptionService->getFixedPrice(
                $price,
                $vat,
                $request->user_id,
                false
            );

            $subscription->update([
                'fixed_price_id' => $fixedPrice->id,
            ]);

            // If fixed price is trashed or soft delete, restore it
            if ($fixedPrice->trashed()) {
                $fixedPrice->restore();
            }
        } elseif ($request->isNotOptional('fixed_price_id')) {
            $subscription->update([
                'fixed_price_id' => $request->fixed_price_id,
            ]);
        }
    }
}
