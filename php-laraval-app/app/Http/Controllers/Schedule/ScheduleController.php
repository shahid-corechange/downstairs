<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Schedule\ScheduleBackOfficeCancelRequestDTO;
use App\DTOs\Schedule\ScheduleBackOfficeUpdateRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Schedule\ScheduleItemPaymentMethodEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Addon;
use App\Models\BlockDay;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Team;
use App\Services\Schedule\ScheduleCleaningService;
use App\Services\Schedule\ScheduleLaundryService;
use App\Services\Schedule\ScheduleService;
use Auth;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'customer',
        'user',
        'team',
        'property.address.city',
        'detail',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'isFixed',
        'hasDeviation',
        'workStatus',
        'startAt',
        'endAt',
        'quarters',
        'status',
        'customer.membershipType',
        'property.address.city.name',
        'user.fullname',
        'team.id',
        'team.color',
        'team.name',
        'team.totalWorkers',
        'detail.laundryOrderId',
        'detail.laundryType',
    ];

    /**
     * More detailed includes for the response.
     */
    private array $detailedIncludes = [
        'allEmployees.user',
        'allEmployees.schedule.team.users',
        'subscription.tasks',
        'property.address.city',
        'refund',
        'team',
        'items.item.tasks',
        'tasks',
        'customer',
        'user',
        'service',
    ];

    /**
     * More detailed onlys for the response.
     */
    private array $detailedOnlys = [
        'id',
        'customerId',
        'teamId',
        'serviceId',
        'userId',
        'startAt',
        'endAt',
        'quarters',
        'keyInformation',
        'notes.propertyNote',
        'notes.subscriptionNote',
        'notes.note',
        'note',
        'isFixed',
        'hasDeviation',
        'workStatus',
        'status',
        'allEmployees.userId',
        'allEmployees.status',
        'allEmployees.deletedAt',
        'allEmployees.user.fullname',
        'allEmployees.schedule.team.users.id',
        'subscription.fixedPriceId',
        'subscription.tasks.id',
        'subscription.tasks.name',
        'subscription.tasks.description',
        'service.name',
        'service.tasks.id',
        'service.tasks.name',
        'service.tasks.description',
        'user.id',
        'user.fullname',
        'user.totalCredits',
        'property.address.city.name',
        'property.address.fullAddress',
        'property.address.latitude',
        'property.address.longitude',
        'property.keyInformation.keyPlace',
        'refund.amount',
        'customer.membershipType',
        'team.id',
        'team.color',
        'team.name',
        'team.totalWorkers',
        'items.paymentMethod',
        'items.itemableType',
        'items.itemableId',
        'items.item.name',
        'items.item.deletedAt',
        'items.item.tasks.id',
        'items.item.tasks.name',
        'items.item.tasks.description',
        'items.item.creditPrice',
        'tasks.id',
        'tasks.name',
        'tasks.description',
        'tasks.translations',
    ];

    public function __construct(
        public ScheduleService $scheduleService,
        public ScheduleCleaningService $scheduleCleaningService,
        public ScheduleLaundryService $scheduleLaundryService,
    ) {
    }

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $timezone = $user->info->timezone;

        // get startAt
        try {
            $startAtQuery = request()->query('startAt_gte');
            $startAt = Carbon::createFromFormat('Y-m-d', $startAtQuery, $timezone)
                ->startOfDay()->utc();
        } catch (InvalidFormatException) {
            $startAt = Carbon::now($timezone)->startOfWeek(Carbon::MONDAY)->utc();
        }

        // get endAt
        try {
            $endAtQuery = request()->query('endAt_lte');
            $endAt = Carbon::createFromFormat('Y-m-d', $endAtQuery, $timezone)
                ->endOfDay()->utc();
        } catch (InvalidFormatException) {
            $endAt = Carbon::now($timezone)->endOfWeek(Carbon::SUNDAY)->utc();
        }

        $queries = $this->getQueries(
            filter: [
                'startAt_gte' => null,
                'endAt_lte' => null,
                'startAt_between' => "{$startAt->clone()->subDays(1)},$endAt", // sub 1 day to get overnight schedules
                'endAt_between' => "$startAt,{$endAt->clone()->addDays(1)}", // add 1 day to get overnight schedules
            ],
            size: -1,
        );

        $paginatedData = Schedule::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        $transport = get_transport();
        $material = get_material();
        $defaultShownTeamIds = array_map(
            'intval',
            explode(',', get_setting(GlobalSettingEnum::DefaultShownTeam(), ''))
        );

        return Inertia::render('Schedule/Overview/index', [
            'schedules' => ScheduleResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'teams' => $this->getTeams(),
            'addons' => $this->getAddOns(),
            'products' => $this->getProducts(),
            'transportPrice' => $transport ? $transport->price_with_vat : 0,
            'materialPrice' => $material ? $material->price_with_vat : 0,
            'defaultShownTeamIds' => $defaultShownTeamIds,
            'defaultMinHourShow' => get_setting(GlobalSettingEnum::DefaultMinHourShow(), '07:00'),
            'defaultMaxHourShow' => get_setting(GlobalSettingEnum::DefaultMaxHourShow(), '18:00'),
            'creditRefundTimeWindow' => get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72),
            'dueDays' => get_setting(GlobalSettingEnum::InvoiceDueDays(), 30),
            'creditExpirationDays' => get_setting(GlobalSettingEnum::CreditExpirationDays(), 365),
            'subscriptionRefillSequence' => get_setting(
                GlobalSettingEnum::SubscriptionRefillSequence(),
                config('downstairs.subscription.refillSequence')
            ),
        ]);
    }

    private function getTeams()
    {
        $onlys = [
            'id',
            'name',
            'avatar',
            'color',
            'totalWorkers',
            'users.id',
            'users.fullname',
        ];
        $teams = Team::selectWithRelations($onlys)
            ->orderBy('name')
            ->get();

        return TeamResponseDTO::collection($teams)
            ->include('users')
            ->only(...$onlys);
    }

    private function getAddOns()
    {
        $onlys = ['id', 'name', 'services.id', 'creditPrice'];
        $addons = Addon::selectWithRelations($onlys)
            ->get();

        return AddonResponseDTO::collection($addons)
            ->include('services')
            ->only(...$onlys);
    }

    private function getProducts()
    {
        $onlys = ['id', 'name', 'creditPrice'];
        $products = Product::selectWithRelations($onlys)
            ->get();

        return ProductResponseDTO::collection($products)
            ->only(...$onlys);
    }

    /**
     * Display the index as a json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        
        // Collect debug info
        $debugInfo = [
            'request_params' => request()->all(),
            'queries' => $queries,
            'userId_filter' => request()->query('userId.eq'),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
        ];
        
        // Log it first
        \Log::warning('Schedule jsonIndex Debug', $debugInfo);
        
        // Force userId filtering for debugging
        if (!isset($queries['filter']['userId.eq']) && request()->query('userId.eq')) {
            $queries['filter']['userId.eq'] = request()->query('userId.eq');
        }
        
        $paginatedData = Schedule::applyFilterSortAndPaginate($queries);

        $response = $this->successResponse(
            ScheduleResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
        
        // Add debug info to response in local environment
        $response->additional(['debug' => $debugInfo]);
        
        return $response;
    }

    /**
     * Display the index as a json.
     */
    public function jsonSchedule(int $scheduleId): JsonResponse
    {
        $data = Schedule::selectWithRelations(mergeFields: true)
            ->findOrFail($scheduleId);

        return $this->successResponse(
            ScheduleResponseDTO::transformData($data),
        );
    }

    /**
     * Cancel the specified schedule.
     */
    public function cancel(
        Schedule $schedule,
        ScheduleBackOfficeCancelRequestDTO $request,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to cancel schedule due to schedule status'),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        if ($request->refund && ! $schedule->can_refund) {
            return $this->errorResponse(
                __('schedule cannot be refunded'),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        if ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Delivery()) {
            return $this->errorResponse(
                __('delivery schedule cannot be canceled'),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        $service = $schedule->isCleaning() ? $this->scheduleCleaningService : $this->scheduleLaundryService;
        $service->cancel($schedule, $request->refund);

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->detailedIncludes,
                onlys: $this->detailedOnlys,
            ),
            message: __('schedule cleaning canceled successfully')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        int $scheduleId,
        ScheduleBackOfficeUpdateRequestDTO $request,
    ): JsonResponse {
        /** @var Schedule $schedule */
        $schedule = Schedule::with(['items', 'scheduleable'])->findOrFail($scheduleId);

        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to update schedule due to schedule status'),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        if (in_array(config('downstairs.addons.laundry.id'), $request->remove_add_ons) &&
            $schedule->scheduleable->laundry_type === ScheduleLaundryTypeEnum::Delivery()
        ) {
            return $this->errorResponse(
                __('can not remove laundry addon from delivery schedule'),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $days = weeks_to_days($refillSequence);
        $refillSequences = SubscriptionRefillSequenceEnum::options();
        $time = array_search($refillSequence, $refillSequences);

        if ($schedule->start_at->isAfter(now()->addDays($days)->endOfDay())) {
            return $this->errorResponse(
                __('failed to update schedule a certain time ahead', [
                    'time' => __($time),
                ]),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        // assign optional values
        $request->assignOptionalValues([
            'team_id' => $schedule->team_id,
            'start_at' => $schedule->start_at,
            'end_at' => $schedule->end_at,
            'remove_products' => [],
            'remove_add_ons' => [],
        ]);

        $startAt = Carbon::parse($request->start_at);
        $endAt = Carbon::parse($request->end_at);

        if ($startAt->notEqualTo($schedule->start_at) || $endAt->notEqualTo($schedule->end_at)) {
            $isBlockDay = BlockDay::where('block_date', $startAt->format('Y-m-d'))
                ->orWhere('block_date', $endAt->format('Y-m-d'))
                ->exists();

            if ($isBlockDay) {
                return $this->errorResponse(
                    __('failed to update schedule due to block day'),
                    HttpResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $isContainLaundryAddon = false;

        if ($request->isNotOptional('new_add_ons')) {
            $isContainLaundryAddon = $request->new_add_ons
                ->toCollection()
                ->contains(fn ($item) => $item->addon_id === config('downstairs.addons.laundry.id'));
        }

        if ($schedule->isCleaning() && $isContainLaundryAddon) {
            $isExists = Schedule::where('scheduleable_type', $schedule->scheduleable_type)
                ->whereNot('scheduleable_id', $schedule->scheduleable_id)
                ->where('status', ScheduleStatusEnum::Booked())
                ->whereHas('scheduleable', function (Builder $query) {
                    $query->whereNull('laundry_order_id');
                })
                ->exists();

            if (! $isExists) {
                return $this->errorResponse(
                    __('can not add laundry addon to schedule because there is no schedule'),
                    HttpResponse::HTTP_BAD_REQUEST
                );
            }
        } elseif ($schedule->isLaundry() && $isContainLaundryAddon) {
            return $this->errorResponse(
                __('can not add laundry addon to laundry schedule'),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        $conflictSchedule = ScheduleService::getConflictSchedule($schedule, $request->team_id, $startAt, $endAt);

        if ($conflictSchedule) {
            return $this->errorResponse(
                __('this action causes conflict with other schedules'),
                HttpResponse::HTTP_CONFLICT
            );
        }

        [$addonsTransactions, $addons] = $this->getAddonsCart($request);
        [$productsTransactions, $products] = $this->getProductsCart($request);

        $cart = [
            'transactions' => array_merge($addonsTransactions, $productsTransactions),
            'items' => array_merge($addons, $products),
        ];

        $service = $schedule->isCleaning() ? $this->scheduleCleaningService : $this->scheduleLaundryService;
        $service->update($schedule, $request, $cart, $startAt, $endAt, $isContainLaundryAddon);

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule->refresh(),
                includes: $this->detailedIncludes,
                onlys: $this->detailedOnlys,
            ),
            message: __('schedule cleaning updated successfully')
        );
    }

    private function getAddonsCart(ScheduleBackOfficeUpdateRequestDTO $request)
    {
        $transactions = [];
        $items = [];

        if ($request->isOptional('new_add_ons')) {
            return [$transactions, $items];
        }

        $addonIds = $request->new_add_ons
            ->toCollection()
            ->map(fn ($item) => $item->addon_id)
            ->toArray();
        /** @var \Illuminate\Support\Collection<int, Addon> $newAddOns */
        $newAddOns = Addon::whereIn('id', $addonIds)->get();

        foreach ($request->new_add_ons->toArray() as $newAddOn) {
            if (! in_array($newAddOn['addon_id'], $request->remove_add_ons)) {
                $addon = $newAddOns->firstWhere('id', $newAddOn['addon_id']);

                if ($newAddOn['use_credit']) {
                    $transactions[] = [
                        'type' => CreditTransactionTypeEnum::Payment(),
                        'amount' => $addon->credit_price,
                        'description' => $addon->name,
                    ];
                }

                $items[] = [
                    'itemable_id' => $addon->id,
                    'itemable_type' => Addon::class,
                    'price' => $addon->price,
                    'quantity' => $newAddOn['quantity'],
                    'discount_percentage' => $newAddOn['use_credit'] ? 100 : 0,
                    'payment_method' => $newAddOn['use_credit'] ?
                        ScheduleItemPaymentMethodEnum::Credit() : ScheduleItemPaymentMethodEnum::Invoice(),
                ];
            }
        }

        return [$transactions, $items];
    }

    private function getProductsCart(ScheduleBackOfficeUpdateRequestDTO $request)
    {
        $transactions = [];
        $items = [];

        if ($request->isOptional('new_products')) {
            return [$transactions, $items];
        }

        $productIds = $request->new_products
            ->toCollection()
            ->map(fn ($item) => $item->product_id)
            ->toArray();
        /** @var \Illuminate\Support\Collection<int, Product> $newProducts */
        $newProducts = Product::whereIn('id', $productIds)->get();

        foreach ($request->new_products->toArray() as $newProduct) {
            if (! in_array($newProduct['product_id'], $request->remove_products)) {
                $product = $newProducts->firstWhere('id', $newProduct['product_id']);

                if ($newProduct['use_credit']) {
                    $transactions[] = [
                        'type' => CreditTransactionTypeEnum::Payment(),
                        'amount' => $product->credit_price,
                        'description' => $product->name,
                    ];
                }

                $items[] = [
                    'itemable_id' => $product->id,
                    'itemable_type' => Product::class,
                    'price' => $product->price,
                    'quantity' => $newProduct['quantity'],
                    'discount_percentage' => $newProduct['use_credit'] ? 100 : 0,
                    'payment_method' => $newProduct['use_credit'] ?
                        ScheduleItemPaymentMethodEnum::Credit() : ScheduleItemPaymentMethodEnum::Invoice(),
                ];
            }
        }

        return [$transactions, $items];
    }
}
