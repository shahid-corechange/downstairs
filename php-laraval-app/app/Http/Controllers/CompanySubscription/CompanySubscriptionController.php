<?php

namespace App\Http\Controllers\CompanySubscription;

use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\Subscription\UpdateSubscriptionRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\UpdateScheduleItemJob;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionCleaningService;
use App\Services\Subscription\SubscriptionLaundryService;
use App\Services\Subscription\SubscriptionNotificationService;
use App\Services\Subscription\SubscriptionService;
use App\Services\Subscription\SubscriptionUpdateService;
use App\Services\Subscription\SubscriptionViewService;
use DB;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanySubscriptionController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionViewService $subscriptionView,
        protected SubscriptionNotificationService $subscriptionNotification,
        protected SubscriptionUpdateService $subscriptionUpdate,
        protected SubscriptionCleaningService $cleaningService,
        protected SubscriptionLaundryService $laundryService,
    ) {
    }

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
        'service',
        'products',
        'addons',
        'updatedSchedules',
        'detail.team',
        'detail.property.address.city.country',
        'detail.pickupTeam',
        'detail.pickupProperty.address.city.country',
        'detail.deliveryTeam',
        'detail.deliveryProperty.address.city.country',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'user.id',
        'user.fullname',
        'service.name',
        'serviceId',
        'fixedPriceId',
        'products.id',
        'addons.id',
        'addons.name',
        'addons.priceWithVat',
        'frequency',
        'weekday',
        'startAt',
        'endAt',
        'startTime',
        'endTime',
        'detail.quarters',
        'detail.teamName',
        'detail.address',
        'detail.startTime',
        'detail.endTime',
        'detail.teamId',
        'detail.quarters',
        'description',
        'isFixed',
        'isPaused',
        'deletedAt',
        'totalRawPrice',
        'updatedSchedules.id',
        'updatedSchedules.startAt',
        'updatedSchedules.endAt',
        'subscribableType',
        'subscribableId',
        'isCleaningHasLaundry',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            filter: [
                'user_roles_name_eq' => 'Company',
                'customer_membershipType_eq' => MembershipTypeEnum::Company(),
            ],
            defaultFilter: [
                'deletedAt_eq' => 'null',
            ],
            pagination: 'page',
            show: 'all',
        );
        $paginatedData = Subscription::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('CompanySubscription/Overview/index', [
            'subscriptions' => SubscriptionResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'frequencies' => $this->subscriptionView->getFrequencies(),
            'teams' => $this->subscriptionView->getTeams(),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
            'services' => $this->subscriptionView->getServices(MembershipTypeEnum::Company()),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            filter: [
                'user_roles_name_eq' => 'Company',
                'customer_membershipType_eq' => MembershipTypeEnum::Company(),
            ],
        );
        $paginatedData = Subscription::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            SubscriptionResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Display the show view as json.
     */
    public function jsonShow(Subscription $subscription): JsonResponse
    {
        if (! $subscription->user->hasRole('Company') ||
            $subscription->customer->membership_type !== MembershipTypeEnum::Company()) {
            throw new NotFoundHttpException();
        }

        return $this->successResponse(
            SubscriptionResponseDTO::transformData($subscription),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionRequestDTO $request, Subscription $subscription)
    {
        $service = $subscription->isCleaning() ? $this->cleaningService : $this->laundryService;
        $isFixed = $subscription->is_fixed;
        $descripton = $subscription->description;

        $service->populates($request, $subscription);
        $service->preprocess($request);
        $isReplace = $service->shouldReplaceSchedules($request, $subscription);

        if ($isReplace) {
            $service->checkCollision($request, null);
        }

        $products = products_request_to_array($request->products->toArray());
        $totalRawPrice = $this->subscriptionUpdate->getTotalRawPrice($request, $subscription);

        DB::transaction(
            function () use (
                $request,
                $subscription,
                $service,
                $totalRawPrice,
                $products,
                $isFixed,
                $descripton,
            ) {
                $service->update($request, $subscription, $isFixed, $descripton);
                $subscription->addons()->sync($request->addon_ids);
                $subscription->products()->sync($products);
                $this->subscriptionUpdate->setFixedPrice($request, $subscription, $totalRawPrice, false);
            }
        );

        $this->subscriptionNotification->sendUpdated($subscription);

        if (! empty($products) || ! empty($request->addon_ids)) {
            UpdateScheduleItemJob::dispatchAfterResponse(
                $subscription,
                $products,
                $request->addon_ids
            );
        }

        return back()->with('success', __('subscription updated successfully'));
    }

    /**
     * Pause the specified resource in storage.
     */
    public function pause(Subscription $subscription)
    {
        DB::transaction(function () use ($subscription) {
            $service = $subscription->isCleaning() ? $this->cleaningService : $this->laundryService;

            $subscription->update(['is_paused' => true]);
            $service->remove($subscription);
        });

        $this->subscriptionNotification->sendPaused($subscription);

        return back()->with('success', __('subscription paused successfully'));
    }

    /**
     * Continue the specified resource in storage.
     */
    public function continue(Subscription $subscription)
    {
        $service = $subscription->isCleaning() ? $this->cleaningService : $this->laundryService;
        $service->checkCollision(null, $subscription);
        $subscription->update(['is_paused' => false]);

        $service->createInitialSchedules($subscription);
        $this->subscriptionNotification->sendContinued($subscription);

        return back()->with('success', __('subscription continued successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        DB::transaction(function () use ($subscription) {
            $service = $subscription->isCleaning() ? $this->cleaningService : $this->laundryService;

            $service->remove($subscription);
            $subscription->delete();
        });

        $this->subscriptionNotification->sendRemoved($subscription);

        return back()->with('success', __('subscription deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Subscription $subscription)
    {
        $service = $subscription->isCleaning() ? $this->cleaningService : $this->laundryService;
        $service->checkCollision(null, $subscription);
        $subscription->restore();

        $service->createInitialSchedules($subscription);

        return back()->with('success', __('subscription restored successfully'));
    }
}
