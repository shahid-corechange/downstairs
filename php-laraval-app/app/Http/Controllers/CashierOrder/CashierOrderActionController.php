<?php

namespace App\Http\Controllers\CashierOrder;

use App\DTOs\LaundryOrder\LaundryOrderChangeStatusRequestDTO;
use App\DTOs\LaundryOrder\LaundryOrderPayRequestDTO;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\LaundryOrder\LaundryOrderHistoryTypeEnum;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SendNotificationJob;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\LaundryOrder;
use App\Services\LaundryOrder\LaundryOrderHistoryService;
use App\Services\Order\OrderStoreLaundryService;
use DB;
use Illuminate\Http\RedirectResponse;

class CashierOrderActionController extends BaseUserController
{
    use ResponseTrait;

    public function __construct(
        private LaundryOrderHistoryService $historyService,
        private OrderStoreLaundryService $orderService,
    ) {
    }

    private array $includes = [
        'store',
        'user',
        'laundryPreference',
        'subscription',
        'customer',
        'pickupProperty.address',
        'pickupTeam',
        'deliveryProperty.address',
        'deliveryTeam',
        'products',
        'schedules',
        'histories.causer',
    ];

    private array $only = [
        'id',
        'userId',
        'storeId',
        'laundryPreferenceId',
        'pickupPropertyId',
        'pickupTeamId',
        'pickupTime',
        'deliveryPropertyId',
        'deliveryTeamId',
        'deliveryTime',
        'message',
        'paymentMethod',
        'meta',
        'orderedAt',
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
        'user.fullname',
        'user.formattedCellphone',
        'customer.name',
        'customer.identityNumber',
        'customer.membershipType',
        'customer.formattedPhone1',
        'customer.address.fullAddress',
        'customer.address.postalCode',
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
        'products.*',
        'histories.createdAt',
        'histories.type',
        'histories.note',
        'histories.causer.fullname',
    ];

    /**
     * Manually change status of a laundry order from cashier
     */
    public function changeStatus(
        LaundryOrderChangeStatusRequestDTO $request,
        LaundryOrder $laundryOrder
    ): RedirectResponse {
        // Get the status progression order
        $statusProgression = [
            LaundryOrderStatusEnum::Pending(),
            LaundryOrderStatusEnum::InProgressPickup(),
            LaundryOrderStatusEnum::PickedUp(),
            LaundryOrderStatusEnum::InProgressStore(),
            LaundryOrderStatusEnum::InProgressLaundry(),
            LaundryOrderStatusEnum::InProgressDelivery(),
            LaundryOrderStatusEnum::Delivered(),
            LaundryOrderStatusEnum::Done(),
            LaundryOrderStatusEnum::Paid(),
            LaundryOrderStatusEnum::Closed(),
        ];

        // Get the current status index
        $currentStatusIndex = array_search($laundryOrder->status, $statusProgression);
        $newStatusIndex = array_search($request->status, $statusProgression);

        // Cannot update if new status is earlier in progression than current status
        if ($newStatusIndex !== false && $newStatusIndex < $currentStatusIndex) {
            return back()->with('error', __('cannot change the laundry order status backwards'));
        }

        // Cannot update if order is already invoiced or done
        if (in_array($laundryOrder->status, [
            LaundryOrderStatusEnum::Closed(),
        ])) {
            return back()->with('error', __('cannot change status of a laundry order'));
        }

        DB::transaction(function () use ($request, $laundryOrder) {
            $laundryOrder->update($request->toArray());

            // Create history
            $this->historyService->create(
                $laundryOrder,
                LaundryOrderHistoryTypeEnum::Order(),
                'Status ändrad till '.strtolower(__($request->status, locale: 'sv_SE'))
            );

            if ($request->isNotOptional('send_message') && $request->send_message) {
                $this->historyService->create(
                    $laundryOrder,
                    LaundryOrderHistoryTypeEnum::Notification(),
                    $request->message
                );
            }
        });

        if ($request->isNotOptional('send_message') && $request->send_message && $request->isNotOptional('message')) {
            $this->sendNotificationToCustomer($laundryOrder, 'updated', $request->message);
        }

        return back()->with('success', __('laundry order set status successfully', ['status' => __($request->status)]));
    }

    /**
     * Action to pay a laundry order from cashier
     */
    public function pay(LaundryOrderPayRequestDTO $request, LaundryOrder $laundryOrder): RedirectResponse
    {
        if (in_array($laundryOrder->status, [
            LaundryOrderStatusEnum::Paid(),
            LaundryOrderStatusEnum::Closed(),
        ])) {
            return back()->with(
                'error',
                __(
                    'laundry order already status',
                    ['status' => strtolower(__($laundryOrder->status))]
                )
            );
        }

        // The payment method must be invoice if the laundry order use schedule
        $paymentMethod = $laundryOrder->payment_method === PaymentMethodEnum::Invoice() ?
            $laundryOrder->payment_method : $request->payment_method;

        // get fixed price from laundry order subscription
        $fixedPrice = $laundryOrder->subscription->fixedPrice;

        if ($fixedPrice && $paymentMethod !== PaymentMethodEnum::Invoice()) {
            return back()->with('error', __('payment method must be invoice if the order has fixed price'));
        }

        if ($laundryOrder->payment_method && $request->payment_method !== $laundryOrder->payment_method) {
            return back()->with(
                'error',
                __('payment method must be the same', ['payment_method' => __($laundryOrder->payment_method)])
            );
        }

        $laundryOrder = DB::transaction(function () use ($request, $laundryOrder, $paymentMethod) {
            $laundryOrder->update([
                ...$request->toArray(),
                'payment_method' => $paymentMethod,
                'status' => LaundryOrderStatusEnum::Closed(),
                'paid_at' => now(),
            ]);

            // Create history
            $this->historyService->create(
                $laundryOrder,
                LaundryOrderHistoryTypeEnum::Order(),
                'Betalning via kassan med '.strtolower(__($request->payment_method, locale: 'sv_SE'))
            );

            // Find or create order and invoice
            scoped_localize('sv_SE', function () use ($laundryOrder) {
                [$order, $invoice] = $this->orderService->createOrder($laundryOrder);
                $this->orderService->createOrderRows($order, $laundryOrder);
                UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
            });

            return $laundryOrder;
        });

        $laundryOrder = LaundryOrderResponseDTO::transformData(
            $laundryOrder,
            includes: $this->includes,
            onlys: $this->only,
        );

        return back()->with([
            'success' => __('laundry order set to paid successfully'),
            'successPayload' => [
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
