<?php

namespace App\Http\Controllers\UnassignSubscription;

use App\DTOs\UnassignSubscription\CreateUnassignSubscriptionRequestDTO;
use App\DTOs\UnassignSubscription\UnassignSubscriptionResponseDTO;
use App\DTOs\UnassignSubscription\UpdateUnassignSubscriptionRequestDTO;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\UnassignSubscription;
use App\Services\Subscription\SubscriptionService;
use App\Services\Subscription\SubscriptionViewService;
use DB;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class UnassignSubscriptionController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected SubscriptionViewService $subscriptionView,
    ) {
    }

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
        'service',
        'customer',
        'addons',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'teamId',
        'serviceId',
        'fixedPrice',
        'frequency',
        'weekday',
        'quarters',
        'propertyAddress',
        'startAt',
        'endAt',
        'startTime',
        'description',
        'isFixed',
        'deletedAt',
        'totalRawPrice',
        'user.id',
        'user.fullname',
        'service.name',
        'customer.membershipType',
        'addons.id',
        'addons.name',
        'cleaningDetail',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            pagination: 'page',
            sort: ['start_at' => 'asc']
        );
        $paginatedData = UnassignSubscription::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );
        $transport = get_transport();
        $material = get_material();

        return Inertia::render('UnassignSubscription/Overview/index', [
            'unassignSubscriptions' => UnassignSubscriptionResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'frequencies' => $this->subscriptionView->getFrequencies(),
            'teams' => $this->subscriptionView->getTeams(),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
            'transportPrice' => $transport ? $transport->price_with_vat : 0,
            'materialPrice' => $material ? $material->price_with_vat : 0,
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = UnassignSubscription::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            UnassignSubscriptionResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUnassignSubscriptionRequestDTO $request)
    {
        $endAt = $request->frequency === SubscriptionFrequencyEnum::Once() ? $request->start_at : $request->end_at;

        if ($request->isNotOptional('cleaning_detail')) {
            // calculate end time using quarters because team not set yet
            $endTime = calculate_end_time(
                $request->cleaning_detail->start_time,
                $request->cleaning_detail->quarters,
            );

            $request->cleaning_detail->end_time = $endTime;
        }

        DB::transaction(
            function () use (
                $request,
                $endAt,
            ) {
                UnassignSubscription::create([
                    ...$request->toArray(),
                    'end_at' => $endAt,
                ]);
            }
        );

        return back()->with('success', __('unassign subscription created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnassignSubscriptionRequestDTO $request, UnassignSubscription $subscription)
    {
        $request->assignOptionalValues([
            'start_at' => $subscription->start_at,
            'end_at' => $subscription->end_at,
        ]);
        $endAt = $request->frequency === SubscriptionFrequencyEnum::Once() ? $request->start_at : $request->end_at;

        if ($request->isNotOptional('cleaning_detail')) {
            // calculate end time using quarters because team not set yet
            $endTime = calculate_end_time(
                $request->cleaning_detail->start_time,
                $request->cleaning_detail->quarters,
            );

            $request->cleaning_detail->end_time = $endTime;
        }

        DB::transaction(
            function () use (
                $request,
                $subscription,
                $endAt,
            ) {
                $subscription->update([
                    ...$request->toArray(),
                    'end_at' => $endAt,
                ]);
            }
        );

        return back()->with('success', __('unassign subscription updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnassignSubscription $subscription)
    {
        $subscription->delete();

        return back()->with('success', __('unassign subscription deleted successfully'));
    }
}
