<?php

namespace App\Http\Controllers\CashierOrder;

use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\DTOs\LaundryOrder\CreateLaundryOrderRequestDTO;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\LaundryOrder\UpdateLaundryOrderRequestDTO;
use App\DTOs\LaundryPreference\LaundryPreferenceResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Enums\LaundryOrder\LaundryOrderHistoryTypeEnum;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\LaundryOrder\CreateLaundryOrderScheduleJob;
use App\Jobs\SendNotificationJob;
use App\Models\Customer;
use App\Models\FixedPrice;
use App\Models\LaundryOrder;
use App\Models\LaundryPreference;
use App\Models\Property;
use App\Models\User;
use App\Services\LaundryOrder\LaundryOrderHistoryService;
use App\Services\LaundryOrder\LaundryOrderService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CashierOrderController extends BaseUserController
{
    use ResponseTrait;

    public function __construct(
        private LaundryOrderHistoryService $historyService,
        private LaundryOrderService $laundryOrderService,
    ) {
    }

    private array $includes = [
        'store',
        'user',
        'laundryPreference',
        'subscription',
        'customer',
        'customer.address',
        'pickupProperty.address',
        'pickupTeam',
        'deliveryProperty.address',
        'deliveryTeam',
        'products',
        'products.product',
        'schedules',
        'histories',
        'histories.causer',
        'pickupInCleaning',
        'pickupInCleaning.property.address',
        'pickupInCleaning.team',
        'deliveryInCleaning',
        'deliveryInCleaning.property.address',
        'deliveryInCleaning.team',
        'scheduleCleanings.scheduleable',
    ];

    private array $only = [
        'id',
        'customerId',
        'userId',
        'storeId',
        'laundryPreferenceId',
        'pickupInCleaningId',
        'pickupPropertyId',
        'pickupTeamId',
        'pickupTime',
        'deliveryInCleaningId',
        'deliveryPropertyId',
        'deliveryTeamId',
        'deliveryTime',
        'message',
        'paymentMethod',
        'orderedAt',
        'dueAt',
        'paidAt',
        'status',
        'totalRut',
        'totalPriceWithVat',
        'totalPriceWithDiscount',
        'totalDiscount',
        'totalVat',
        'totalToPay',
        'roundAmount',
        'preferenceAmount',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'store.id',
        'store.name',
        'user.id',
        'user.firstName',
        'user.lastName',
        'user.fullname',
        'user.email',
        'user.formattedCellphone',
        'customer.name',
        'customer.identityNumber',
        'customer.membershipType',
        'customer.formattedPhone1',
        'customer.address.fullAddress',
        'customer.address.postalCode',
        'customer.invoiceMethod',
        'laundryPreference.name',
        'laundryPreference.price',
        'laundryPreference.percentage',
        'laundryPreference.hours',
        'pickupProperty.address.fullAddress',
        'pickupTeam.name',
        'pickupTeam.id',
        'deliveryProperty.address.fullAddress',
        'deliveryTeam.name',
        'deliveryTeam.id',
        'products.id',
        'products.productId',
        'products.name',
        'products.note',
        'products.quantity',
        'products.price',
        'products.discount',
        'products.vatGroup',
        'products.hasRut',
        'products.priceWithVat',
        'products.totalPriceWithVat',
        'products.totalDiscountAmount',
        'products.totalVatAmount',
        'products.totalPriceWithDiscount',
        'products.totalRut',
        'products.product.priceWithVat',
        'histories.createdAt',
        'histories.type',
        'histories.note',
        'histories.causer.fullname',
        'pickupInCleaning.startAt',
        'pickupInCleaning.endAt',
        'pickupInCleaning.property.address.fullAddress',
        'pickupInCleaning.team.name',
        'deliveryInCleaning.startAt',
        'deliveryInCleaning.endAt',
        'deliveryInCleaning.property.address.fullAddress',
        'deliveryInCleaning.team.name',
        'scheduleCleanings.id',
        'scheduleCleanings.scheduleableId',
        'scheduleCleanings.scheduleableType',
        'scheduleCleanings.scheduleable.laundryType',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $laundryPreferences = $this->getPreferences();

        return Inertia::render('CashierOrder/index', [
            'laundryPreferences' => $laundryPreferences,
        ]);
    }

    /**
     * Display the index customer view.
     */
    public function indexCustomer(User $user): Response
    {
        $laundryPreferences = $this->getPreferences();

        return Inertia::render('CashierCustomer/Orders/Overview/index', [
            'customer' => UserResponseDTO::transformData($user),
            'laundryPreferences' => $laundryPreferences,
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            defaultFilter: [
                'store_id_eq' => $storeId,
            ],
            sort: ['ordered_at' => 'desc'],
        );
        $paginatedData = LaundryOrder::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            LaundryOrderResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Show a customer order.
     */
    public function showCustomerOrder(User $user, LaundryOrder $laundryOrder): Response
    {
        $laundryOrder = LaundryOrderResponseDTO::transformData($laundryOrder, $this->includes, onlys: $this->only);

        return Inertia::render('CashierCustomer/Orders/OrderDetails/index', [
            'customer' => $this->getCustomer($user),
            'laundryOrder' => $laundryOrder,
            'laundryPreferences' => $this->getPreferences(),
            'fixedPrice' => $this->getFixedPrice($user),
        ]);
    }

    /**
     * Get the customer.
     */
    public function getCustomer(User $user)
    {
        $includes = [
            'info',
        ];

        return UserResponseDTO::transformData($user, $includes);
    }

    /**
     * Get the laundry preferences.
     */
    public function getPreferences()
    {
        $onlys = [
            'id',
            'name',
            'description',
            'vat_group',
            'hours',
            'price_with_vat',
            'price',
            'percentage',
        ];
        $query = LaundryPreference::selectWithRelations($onlys);

        $laundryPreferences = $query->get();

        return LaundryPreferenceResponseDTO::collection($laundryPreferences)
            ->only(...$onlys);
    }

    private function getFixedPrice(User $user)
    {
        $includes = [
            'rows',
            'laundryProducts',
        ];
        $onlys = [
            'rows.type',
            'rows.priceWithVat',
            'laundryProducts.id',
        ];
        $fixedPrice = FixedPrice::getActive(
            $user->id,
            [FixedPriceTypeEnum::Laundry(), FixedPriceTypeEnum::CleaningAndLaundry()],
            now(),
            now()->endOfMonth()
        );

        return $fixedPrice
            ? FixedPriceResponseDTO::transformData(
                $fixedPrice,
                $includes,
                onlys: $onlys,
            )
            : null;
    }

    /**
     * Show card payment view.
     */
    public function cardPayment(User $user, LaundryOrder $laundryOrder): RedirectResponse|Response
    {
        // Redirect to invoice payment if payment method is invoice
        if ($laundryOrder->payment_method === PaymentMethodEnum::Invoice()) {
            return redirect()->route('cashier.customers.orders.invoice-payment', [$user->id, $laundryOrder->id]);
        }

        $customer = UserResponseDTO::transformData($user);
        $laundryOrder = LaundryOrderResponseDTO::transformData($laundryOrder, $this->includes, onlys: $this->only);

        return Inertia::render('CashierCustomer/Orders/CardPayment/index', [
            'customer' => $customer,
            'laundryOrder' => $laundryOrder,
        ]);
    }

    /**
     * Show invoice payment view.
     */
    public function invoicePayment(User $user, LaundryOrder $laundryOrder): Response
    {
        $customer = UserResponseDTO::transformData($user);
        $laundryOrder = LaundryOrderResponseDTO::transformData($laundryOrder, $this->includes, onlys: $this->only);

        return Inertia::render('CashierCustomer/Orders/InvoicePayment/index', [
            'customer' => $customer,
            'laundryOrder' => $laundryOrder,
        ]);
    }

    /**
     * Store a new order laundry
     */
    public function store(CreateLaundryOrderRequestDTO $request): RedirectResponse
    {
        $user = User::find($request->user_id);
        /** @var Property $property */
        $property = $user->properties()->first();

        // validation cannot create schedule for pickup and delivery if property is not set
        if (! $property && ($request->pickup_property_id || $request->delivery_property_id)) {
            return back()->with('error', __('cannot create schedule for pickup and delivery'));
        }

        $storeId = request()->session()->get('store_id');
        // if pickup or delivery property is set, payment method must be invoice
        $paymentMethod = $request->pickup_property_id || $request->delivery_property_id ?
            PaymentMethodEnum::Invoice() : null;
        $status = $paymentMethod ? LaundryOrderStatusEnum::Pending() :
            LaundryOrderStatusEnum::InProgressStore();
        /** @var Customer $customer */
        $customer = $user->customers()->where('type', ContactTypeEnum::Primary())->first();

        // update product id to product sales misc if is modified and product id is 0
        $request->products->map(function ($product) {
            if ($product->is_modified || $product->product_id === 0) {
                $product->product_id = config('downstairs.products.productSalesMisc.id');
            }

            return $product;
        });

        $orderedAt = Carbon::parse($request->ordered_at)
            ->setTimeFromTimeString($request->pickup_time ?? now()->format('H:i:s'))
            ->toDateTimeString();

        $laundryOrder = DB::transaction(function () use (
            $request,
            $storeId,
            $paymentMethod,
            $status,
            $customer,
            $orderedAt,
        ) {
            $laundryOrder = LaundryOrder::create([
                ...$request->toArray(),
                'store_id' => $storeId,
                'ordered_at' => $orderedAt,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'customer_id' => $customer->id,
                'causer_id' => auth()->user()->id,
            ]);

            $laundryOrder->products()->createMany($request->products->toArray());
            $this->historyService->addCreateHistory($laundryOrder, $request->products);

            if ($request->send_message) {
                $this->historyService->create(
                    $laundryOrder,
                    LaundryOrderHistoryTypeEnum::Notification(),
                    $request->message
                );
            }

            return $laundryOrder;
        });

        if ($request->send_message) {
            $this->sendNotificationToCustomer($laundryOrder, 'created', $request->message);
        }

        // if pickup or delivery property is set, add schedule for pickup and delivery
        if ($laundryOrder->pickup_property_id || $laundryOrder->delivery_property_id) {
            $schedules = $this->laundryOrderService->composeSchedules($laundryOrder);

            foreach ($schedules as $schedule) {
                CreateLaundryOrderScheduleJob::dispatch($schedule, $laundryOrder);
            }
        }

        return redirect()
            ->route('cashier.customers.orders.show', [$laundryOrder->user_id, $laundryOrder->id])
            ->with('success', __('laundry order created successfully'));
    }

    /**
     * Update an order laundry
     */
    public function update(UpdateLaundryOrderRequestDTO $request, LaundryOrder $laundryOrder): RedirectResponse
    {
        $storeId = request()->session()->get('store_id');
        if ($laundryOrder->store_id !== $storeId) {
            return back()->with('error', __('laundry order not found'));
        }

        // cannot allow update order
        if (in_array($laundryOrder->status, [
            LaundryOrderStatusEnum::InProgressDelivery(),
            LaundryOrderStatusEnum::Delivered(),
            LaundryOrderStatusEnum::Done(),
            LaundryOrderStatusEnum::Paid(),
            LaundryOrderStatusEnum::Closed(),
        ])) {
            return back()->with('error', __('laundry order cannot be updated'));
        }

        $oldData = $laundryOrder->toArray();

        // assign optional values to request
        $request->assignOptionalValues([
            'pickup_property_id' => $laundryOrder->pickup_property_id,
            'pickup_team_id' => $laundryOrder->pickup_team_id,
            'pickup_time' => $laundryOrder->pickup_time,
            'delivery_property_id' => $laundryOrder->delivery_property_id,
            'delivery_team_id' => $laundryOrder->delivery_team_id,
            'delivery_time' => $laundryOrder->delivery_time,
            'products' => null,
        ]);

        // update product id to product sales misc if is modified and product id is 0
        $request->products->map(function ($product) {
            if ($product->is_modified || $product->product_id === 0) {
                $product->product_id = config('downstairs.products.productSalesMisc.id');
            }

            return $product;
        });

        $laundryOrder = DB::transaction(function () use ($request, $laundryOrder) {
            $laundryOrder->update($request->toArray());
            if ($request->products) {
                $laundryOrder->syncProducts($request->products->toArray());
            }

            $this->historyService->addUpdateHistory($laundryOrder, $request->products);

            if ($request->send_message) {
                $this->historyService->create(
                    $laundryOrder,
                    LaundryOrderHistoryTypeEnum::Notification(),
                    $request->message
                );
            }

            return $laundryOrder;
        });

        if ($request->send_message) {
            $this->sendNotificationToCustomer($laundryOrder, 'updated', $request->message);
        }

        $this->laundryOrderService->updateTeams($laundryOrder, $oldData);
        $customer = UserResponseDTO::transformData($laundryOrder->user);
        $laundryOrder = LaundryOrderResponseDTO::transformData(
            $laundryOrder,
            includes: $this->includes,
            onlys: $this->only,
        );

        return back()->with([
            'success' => __('laundry order updated successfully'),
            'successPayload' => [
                'customer' => $customer,
                'laundryOrder' => $laundryOrder,
            ],
        ]);
    }

    /**
     * Send notification to customer
     *
     * @param  LaundryOrder  $laundryOrder
     * @param  string  $type
     * @param  string  $message
     */
    private function sendNotificationToCustomer($laundryOrder, $type, $message)
    {
        $user = $laundryOrder->user;
        $notificationType = $type === 'created' ? NotificationTypeEnum::OrderLaundryCreated() :
            NotificationTypeEnum::OrderLaundryUpdated();

        scoped_localize(
            $user->info->language,
            function () use ($user, $notificationType, $message, $type) {
                SendNotificationJob::dispatchAfterResponse(
                    $user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            $notificationType,
                            payload: [],
                        ),
                        title: __("notification title laundry order {$type}"),
                        body: $message,
                        shouldSave: true,
                        shouldInferMethod: true,
                    ),
                );
            }
        );
    }
}
