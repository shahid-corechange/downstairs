<?php

namespace App\Http\Controllers\CashierOrder;

use App\DTOs\LaundryOrder\CreateOrderRequestDTO;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\LaundryOrder\UpdateOrderRequestDTO;
use App\DTOs\LaundryOrderProduct\LaundryOrderProductRequestDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Enums\LaundryOrder\LaundryOrderHistoryTypeEnum;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\User\BaseUserController;
use App\Jobs\SendNotificationJob;
use App\Models\Addon;
use App\Models\Customer;
use App\Models\CustomerDiscount;
use App\Models\FixedPrice;
use App\Models\LaundryOrder;
use App\Models\LaundryPreference;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Subscription;
use App\Models\SubscriptionLaundryDetail;
use App\Models\User;
use App\Services\DiscountService;
use App\Services\LaundryOrder\LaundryOrderHistoryService;
use App\Services\LaundryOrder\LaundryOrderService;
use App\Services\Schedule\ScheduleNoteService;
use App\Services\Schedule\ScheduleTaskService;
use DB;
use Illuminate\Http\RedirectResponse;
use Spatie\LaravelData\DataCollection;

class CashierScheduleOrderController extends BaseUserController
{
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
     * Store a new order laundry
     */
    public function store(CreateOrderRequestDTO $request): RedirectResponse
    {
        $user = User::find($request->user_id);
        $storeId = request()->session()->get('store_id');

        // Get preference
        $preference = LaundryPreference::find($request->laundry_preference_id);
        $orderedAt = now();

        try {
            if ($request->pickup_schedule_id || $request->delivery_schedule_id) {
                if ($request->pickup_schedule_id) {
                    $pickupSchedule = $this->getSchedule(
                        $request->pickup_schedule_id,
                        $orderedAt,
                        ScheduleLaundryTypeEnum::Pickup(),
                    );

                    // Check delivery schedule is after pickup schedule
                    if ($request->delivery_schedule_id) {
                        $deliverySchedule = $this->getSchedule(
                            $request->delivery_schedule_id,
                            $pickupSchedule->start_at->copy()->addHours($preference->hours),
                            ScheduleLaundryTypeEnum::Delivery(),
                        );
                    }

                    $orderedAt = $pickupSchedule->start_at;
                    $customerId = $pickupSchedule->customer_id;
                } else {
                    $deliverySchedule = $this->getSchedule(
                        $request->delivery_schedule_id,
                        $orderedAt,
                        ScheduleLaundryTypeEnum::Delivery(),
                    );
                    $customerId = $deliverySchedule->customer_id;
                }
            } else {
                $customerId = $user->customers()->where('type', ContactTypeEnum::Primary())->first()->id;
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $customer = Customer::find($customerId);

        $status = $request->pickup_schedule_id ? LaundryOrderStatusEnum::Pending() :
            LaundryOrderStatusEnum::InProgressStore();

        // get fixed price for the month of the ordered at
        $fixedPrice = FixedPrice::getActive(
            $request->user_id,
            [FixedPriceTypeEnum::CleaningAndLaundry(), FixedPriceTypeEnum::Laundry()],
            $orderedAt,
            $orderedAt->copy()->endOfMonth(),
        );

        $fixedPriceProductIds = $fixedPrice ? $fixedPrice->laundryProducts->pluck('id')->toArray() : [];

        // Fetch the updated discount if available
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $request->user_id,
            CustomerDiscountTypeEnum::Laundry(),
        );

        // Fetch the updated products
        $sourceProducts = $this->getNotModifiedProducts($request->products, $storeId);

        $laundryOrderProducts = [];
        $subscriptionProducts = [];
        $isFixedPriceUsed = false;

        foreach ($request->products->toArray() as $product) {
            /** @var Product|null $sourceProduct */
            $sourceProduct = $sourceProducts->firstWhere('id', $product['product_id']);

            // update product id to product sales misc if is modified and product id is 0
            if ($product['is_modified'] || $product['product_id'] === 0) {
                $product['product_id'] = config('downstairs.products.productSalesMisc.id');
            } elseif (! $sourceProduct) {
                // don't include product if product is not found
                continue;
            } elseif (! $product['is_modified'] && $discount) {
                // if product is not modified and discount is available, set discount to discount value
                $product['discount'] = $discount->value;
            } elseif ($sourceProduct) {
                $product['has_rut'] = $customer->isPrivate() ? $sourceProduct->has_rut : false;
                $product['vat_group'] = $sourceProduct->vat_group;
            }

            // if product include in fixed price or fixed price include all products, set price to 0
            // except product sales misc
            if ($fixedPrice &&
                (in_array($product['product_id'], $fixedPriceProductIds) || empty($fixedPriceProductIds))
                && $product['product_id'] !== config('downstairs.products.productSalesMisc.id')
            ) {
                $product['price'] = 0;
                $isFixedPriceUsed = true;
            } elseif ($sourceProduct) {
                $product['price'] = $sourceProduct->price;
            }

            $laundryOrderProducts[] = $product;
            $subscriptionProducts[] = [
                ...$product,
                'itemable_id' => $product['product_id'],
                'itemable_type' => Product::class,
            ];
        }

        if ($isFixedPriceUsed &&
            $request->laundry_preference_id !== config('downstairs.laundry.preference.normal.id')) {
            return back()->with('error', __('must select normal laundry preference if have fixed price'));
        }

        // if pickup or delivery is set or fixed price is used, payment method must be invoice
        $paymentMethod = $isFixedPriceUsed || $request->pickup_schedule_id || $request->delivery_schedule_id ?
            PaymentMethodEnum::Invoice() : null;

        // select service for subscription
        $serviceId = $customer->membership_type === MembershipTypeEnum::Company() ?
            config('downstairs.services.laundry.company.id') :
            config('downstairs.services.laundry.private.id');

        $laundryOrder = DB::transaction(function () use (
            $request,
            $storeId,
            $paymentMethod,
            $status,
            $customerId,
            $orderedAt,
            $serviceId,
            $fixedPrice,
            $laundryOrderProducts,
            $subscriptionProducts,
            $isFixedPriceUsed,
            $discount,
        ) {

            // Initialize subscription to be able use fixed price
            $subscriptionDetail = SubscriptionLaundryDetail::create([
                'store_id' => $storeId,
                'laundry_preference_id' => $request->laundry_preference_id,
                'pickup_time' => $orderedAt->format('H:i:s'),
            ]);

            $subscription = Subscription::create([
                'user_id' => $request->user_id,
                'customer_id' => $customerId,
                'service_id' => $serviceId,
                'frequency' => SubscriptionFrequencyEnum::Once(),
                'subscribable_type' => SubscriptionLaundryDetail::class,
                'subscribable_id' => $subscriptionDetail->id,
                'start_at' => $orderedAt->format('Y-m-d'),
                'end_at' => $orderedAt->format('Y-m-d'),
                'status' => SubscriptionStatusEnum::Active(),
                'fixed_price_id' => $isFixedPriceUsed ? $fixedPrice?->id : null,
            ]);

            // Add subscription items to define products that are covered by fixed price
            $subscription->items()->createMany($subscriptionProducts);

            $laundryOrder = LaundryOrder::create([
                ...$request->toArray(),
                'store_id' => $storeId,
                'ordered_at' => $orderedAt,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'customer_id' => $customerId,
                'causer_id' => auth()->user()->id,
                'subscription_id' => $subscription->id,
            ]);

            $laundryOrder->products()->createMany($laundryOrderProducts);
            $this->historyService->addCreateHistory($laundryOrder, collect($laundryOrderProducts));

            if ($request->send_message) {
                // replace IDN with laundry order id
                $message = str_replace('IDN', $laundryOrder->id, $request->message);
                $this->historyService->create(
                    $laundryOrder,
                    LaundryOrderHistoryTypeEnum::Notification(),
                    $message
                );
            }

            // add pickup schedule to laundry order
            if ($request->pickup_schedule_id) {
                $pickupSchedule = $this->addSchedule(
                    $request->pickup_schedule_id,
                    ScheduleLaundryTypeEnum::Pickup(),
                    $laundryOrder,
                );

                // update subscription detail
                $subscriptionDetail->update([
                    'pickup_team_id' => $pickupSchedule->team_id,
                    'pickup_property_id' => $pickupSchedule->property_id,
                    'pickup_time' => $pickupSchedule->start_at->format('H:i:s'),
                ]);
            }

            // add delivery schedule to laundry order
            if ($request->delivery_schedule_id) {
                $deliverySchedule = $this->addSchedule(
                    $request->delivery_schedule_id,
                    ScheduleLaundryTypeEnum::Delivery(),
                    $laundryOrder,
                );

                // update subscription detail
                $subscriptionDetail->update([
                    'delivery_team_id' => $deliverySchedule->team_id,
                    'delivery_property_id' => $deliverySchedule->property_id,
                    'delivery_time' => $deliverySchedule->start_at->format('H:i:s'),
                ]);
            }

            // use discount if exists
            if ($discount) {
                DiscountService::useDiscount($discount);
            }

            return $laundryOrder;
        });

        if ($request->send_message) {
            // replace IDN with laundry order id
            $message = str_replace('IDN', $laundryOrder->id, $request->message);
            $this->sendNotificationToCustomer($laundryOrder, 'created', $message);
        }

        return redirect()
            ->route('cashier.customers.orders.show', [$laundryOrder->user_id, $laundryOrder->id])
            ->with('success', __('laundry order created successfully'));
    }

    /**
     * Update an order laundry
     */
    public function update(UpdateOrderRequestDTO $request, LaundryOrder $laundryOrder): RedirectResponse
    {
        $storeId = request()->session()->get('store_id');

        if ($laundryOrder->store_id !== $storeId) {
            return back()->with('error', __('laundry order not found'));
        }

        // cannot allow update order
        if (in_array($laundryOrder->status, [
            LaundryOrderStatusEnum::Paid(),
            LaundryOrderStatusEnum::Closed(),
        ])) {
            return back()->with('error', __('laundry order cannot be updated'));
        }

        $oldPickupScheduleId = $laundryOrder->pickup_in_cleaning_id;
        $oldDeliveryScheduleId = $laundryOrder->delivery_in_cleaning_id;

        // assign optional values to request
        $request->assignOptionalValues([
            'pickup_schedule_id' => null,
            'delivery_schedule_id' => null,
            'products' => null,
            'laundry_preference_id' => $laundryOrder->laundry_preference_id,
        ]);

        $isFixedPriceUsed = false;
        $laundryOrderProducts = [];
        $subscriptionProducts = [];
        $fixedPrice = $laundryOrder->subscription?->fixedPrice;

        if ($request->products) {
            if (! $fixedPrice) {
                // Try to get fixed price for the month of the ordered at
                $fixedPrice = FixedPrice::getActive(
                    $request->user_id,
                    [FixedPriceTypeEnum::CleaningAndLaundry(), FixedPriceTypeEnum::Laundry()],
                    $laundryOrder->ordered_at,
                    $laundryOrder->ordered_at->copy()->endOfMonth(),
                );
            }

            $sourceProducts = $this->getNotModifiedProducts($request->products, $storeId);

            $fixedPriceProductIds = $fixedPrice ? $fixedPrice->laundryProducts->pluck('id')->toArray() : [];

            foreach ($request->products->toArray() as $product) {
                /** @var Product|null $sourceProduct */
                $sourceProduct = $sourceProducts->firstWhere('id', $product['product_id']);

                // update product id to product sales misc if is modified and product id is 0
                if ($product['is_modified'] || $product['product_id'] === 0) {
                    $product['product_id'] = config('downstairs.products.productSalesMisc.id');
                } elseif (! $sourceProduct) {
                    // don't include product if product is not found
                    continue;
                } elseif ($sourceProduct) {
                    $product['has_rut'] = $laundryOrder->customer->isPrivate() ? $sourceProduct->has_rut : false;
                    $product['vat_group'] = $sourceProduct->vat_group;
                }

                // if product include in fixed price or fixed price include all products, set price to 0
                // except product sales misc
                if ($fixedPrice &&
                    (in_array($product['product_id'], $fixedPriceProductIds) || empty($fixedPriceProductIds))
                    && $product['product_id'] !== config('downstairs.products.productSalesMisc.id')
                ) {
                    $product['price'] = 0;
                    $isFixedPriceUsed = true;
                } elseif ($sourceProduct) {
                    $product['price'] = $sourceProduct->price;
                }

                $laundryOrderProducts[] = $product;
                $subscriptionProducts[] = [
                    ...$product,
                    'itemable_id' => $product['product_id'],
                    'itemable_type' => Product::class,
                ];
            }
        }

        if ($isFixedPriceUsed &&
            $request->laundry_preference_id !== config('downstairs.laundry.preference.normal.id')) {
            return back()->with('error', __('must select normal laundry preference if have fixed price'));
        }

        try {
            if ($request->pickup_schedule_id !== $oldPickupScheduleId
                && $laundryOrder->status === LaundryOrderStatusEnum::Pending()) {
                // if pickup schedule is set, set ordered at to pickup schedule start at
                if ($request->pickup_schedule_id) {
                    $pickupSchedule = $this->getSchedule(
                        $request->pickup_schedule_id,
                        now(),
                        ScheduleLaundryTypeEnum::Pickup(),
                    );

                    $orderedAt = $pickupSchedule->start_at;
                    $status = LaundryOrderStatusEnum::Pending();
                    $paymentMethod = PaymentMethodEnum::Invoice();
                } else {
                    // if pickup schedule is removed, set ordered at to now
                    $orderedAt = now();
                    $status = LaundryOrderStatusEnum::InProgressStore();
                    $paymentMethod = null;
                }
            } else {
                $orderedAt = $laundryOrder->ordered_at;
                $status = $laundryOrder->status;
                $paymentMethod = $laundryOrder->payment_method;
            }

            if ($request->delivery_schedule_id &&
                $request->pickup_schedule_id !== $oldPickupScheduleId &&
                ! in_array($laundryOrder->status, [
                    LaundryOrderStatusEnum::InProgressDelivery(),
                    LaundryOrderStatusEnum::Delivered(),
                    LaundryOrderStatusEnum::Done(),
                    LaundryOrderStatusEnum::Paid(),
                    LaundryOrderStatusEnum::Closed(),
                ])) {
                // Difference between delivery schedule and ordered
                // at is less than laundry preference hours
                $preference = LaundryPreference::find($request->laundry_preference_id);
                $this->getSchedule(
                    $request->delivery_schedule_id,
                    $orderedAt->copy()->addHours($preference->hours),
                    ScheduleLaundryTypeEnum::Delivery(),
                );
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $laundryOrder = DB::transaction(function () use (
            $request,
            $laundryOrder,
            $oldPickupScheduleId,
            $oldDeliveryScheduleId,
            $orderedAt,
            $status,
            $paymentMethod,
            $subscriptionProducts,
            $laundryOrderProducts,
            $isFixedPriceUsed,
            $fixedPrice,
        ) {
            $laundryOrder->update([
                ...$request->toArray(),
                'ordered_at' => $orderedAt,
                'status' => $status,
                'payment_method' => $isFixedPriceUsed ? PaymentMethodEnum::Invoice() : $paymentMethod,
                'laundry_preference_id' => $isFixedPriceUsed ? config('downstairs.laundry.preference.normal.id') :
                    $request->laundry_preference_id,
            ]);

            if ($laundryOrder->subscription && $fixedPrice && $isFixedPriceUsed) {
                $laundryOrder->subscription->update([
                    'fixed_price_id' => $fixedPrice->id,
                ]);
            }

            // update products
            if ($request->products) {
                $laundryOrder->syncProducts($laundryOrderProducts);
            }

            $this->historyService->addUpdateHistory($laundryOrder, collect($laundryOrderProducts));

            if ($request->send_message) {
                $this->historyService->create(
                    $laundryOrder,
                    LaundryOrderHistoryTypeEnum::Notification(),
                    $request->message
                );
            }

            // add or remove pickup schedule to laundry order
            if ($request->pickup_schedule_id !== $oldPickupScheduleId) {
                if ($oldPickupScheduleId) {
                    $this->removeSchedule($oldPickupScheduleId);
                }

                /**
                 * Remove delivery schedule if pickup schedule is changed to the same delivery schedule
                 * before adding new pickup schedule
                 */
                if ($oldDeliveryScheduleId === $request->pickup_schedule_id) {
                    $this->removeSchedule($oldDeliveryScheduleId);
                }

                if ($request->pickup_schedule_id) {
                    $pickupSchedule = $this->addSchedule(
                        $request->pickup_schedule_id,
                        ScheduleLaundryTypeEnum::Pickup(),
                        $laundryOrder,
                    );

                    // update subscription detail
                    $laundryOrder->subscription->subscribable()->update([
                        'pickup_team_id' => $pickupSchedule->team_id,
                        'pickup_property_id' => $pickupSchedule->property_id,
                        'pickup_time' => $pickupSchedule->start_at->format('H:i:s'),
                    ]);
                }
            }

            // add or remove delivery schedule to laundry order
            if ($request->delivery_schedule_id !== $oldDeliveryScheduleId) {
                if ($oldDeliveryScheduleId && $oldDeliveryScheduleId !== $request->pickup_schedule_id) {
                    $this->removeSchedule($oldDeliveryScheduleId);
                }

                if ($request->delivery_schedule_id) {
                    $deliverySchedule = $this->addSchedule(
                        $request->delivery_schedule_id,
                        ScheduleLaundryTypeEnum::Delivery(),
                        $laundryOrder,
                    );

                    // update subscription detail
                    $laundryOrder->subscription->subscribable()->update([
                        'delivery_team_id' => $deliverySchedule->team_id,
                        'delivery_property_id' => $deliverySchedule->property_id,
                        'delivery_time' => $deliverySchedule->start_at->format('H:i:s'),
                    ]);
                }
            }

            // update subscription items if frequency is once
            if ($laundryOrder->subscription->frequency === SubscriptionFrequencyEnum::Once()) {
                $laundryOrder->subscription
                    ->items()
                    ->where('itemable_type', Product::class)
                    ->delete();

                $laundryOrder->subscription
                    ->items()
                    ->createMany($subscriptionProducts);
            }

            return $laundryOrder;
        });

        if ($request->send_message) {
            $this->sendNotificationToCustomer($laundryOrder, 'updated', $request->message);
        }

        // Uncomment this if allow to update paid order
        // if ($laundryOrder->status === LaundryOrderStatusEnum::Paid()) {
        //     UpdateLaundryOrderInvoiceJob::dispatchAfterResponse($laundryOrder);
        // }

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

    /**
     * Get schedule and check if schedule is after particular time
     *
     * @param  int  $id
     * @param  Carbon  $startAt
     * @param  string  $type
     * @return Schedule
     */
    private function getSchedule($id, $startAt, $type)
    {
        $schedule = Schedule::with('scheduleable')->find($id);

        if (! $schedule) {
            throw new \Exception(__('schedule not found or deleted'));
        } elseif ($schedule->status !== ScheduleStatusEnum::Booked()) {
            throw new \Exception(__('schedule is not booked'));
        } elseif ($schedule->start_at->isBefore($startAt)) {
            $displayDateTime = $startAt->format('Y-m-d H:i:s');
            throw new \Exception(__('schedule must be after particular time', [
                'type' => $type,
                'time' => $displayDateTime,
            ]));
        } elseif ($schedule->scheduleable->laundry_order_id) {
            throw new \Exception(__('schedule already used in another laundry order'));
        }

        return $schedule;
    }

    /**
     * Add schedule to laundry order
     *
     * @param  int  $id
     * @param  string  $type pickup or delivery
     * @param  LaundryOrder  $laundryOrder
     */
    private function addSchedule($id, $type, $laundryOrder): Schedule
    {
        $schedule = Schedule::find($id);
        $schedule->scheduleable()->update([
            'laundry_order_id' => $laundryOrder->id,
            'laundry_type' => $type,
        ]);

        $schedule->items()->create([
            'itemable_id' => config('downstairs.addons.laundry.id'),
            'itemable_type' => Addon::class,
            'price' => 0,
            'quantity' => 1,
            'discount_percentage' => 0,
            'payment_method' => PaymentMethodEnum::Invoice(),
        ]);

        ScheduleTaskService::addLaundryTask($schedule, $laundryOrder, $type);
        ScheduleNoteService::addLaundryNote($schedule, $laundryOrder, $type);

        return $schedule;
    }

    /**
     * Remove schedule from laundry order
     *
     * @param  int  $id
     */
    private function removeSchedule($id)
    {
        $schedule = Schedule::find($id);
        $schedule->scheduleable()->update([
            'laundry_order_id' => null,
            'laundry_type' => null,
        ]);

        $schedule->items()
            ->where('itemable_type', Addon::class)
            ->where('itemable_id', config('downstairs.addons.laundry.id'))
            ->delete();

        ScheduleTaskService::removeLaundryTask($schedule);
        ScheduleNoteService::removeLaundryNote($schedule);
    }

    /**
     * Get not modified product ids
     *
     * @param  DataCollection  $products
     * @param  int  $storeId
     */
    private function getNotModifiedProducts($products, $storeId)
    {
        // Get not modified product ids
        $notModifiedProductIds = $products->reduce(function ($carry, LaundryOrderProductRequestDTO $product) {
            if (! $product->is_modified) {
                $carry[] = $product->product_id;
            }

            return $carry;
        }, []);

        return Product::whereIn('id', $notModifiedProductIds)
            ->whereHas('stores', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->get();
    }
}
