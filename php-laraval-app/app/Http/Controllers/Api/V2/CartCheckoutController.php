<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Cart\CreateCartAddOnRequestDTO;
use App\DTOs\Cart\CreateCheckoutRequestDTO;
use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Exceptions\ErrorResponseException;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\LaundryOrder\CreateLaundryOrderFromCartJob;
use App\Jobs\SendNotificationJob;
use App\Models\Addon;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use App\Services\CreditService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class CartCheckoutController extends Controller
{
    use ResponseTrait;

    /**
     * Checkout the user cart.
     */
    public function store(CreateCheckoutRequestDTO $request, CreditService $creditService): Response
    {
        $cartData = $this->getCartItems($request);
        $this->validateCartData($cartData);

        DB::transaction(function () use ($creditService, $cartData) {
            foreach ($cartData['cart'] as $value) {
                /** @var \App\Models\Schedule */
                $schedule = $value['schedule'];
                /** @var array */
                $addons = $value['addons'];

                $schedule->items()->createMany($addons);

                foreach ($value['transactions'] as $transaction) {
                    $creditService->createTransaction(
                        Auth::id(),
                        $transaction['type'],
                        $transaction['amount'],
                        $transaction['description'],
                        $transaction['schedule_id']
                    );
                }
            }
        });

        if (count($cartData['laundryScheduleIds']) > 0) {
            CreateLaundryOrderFromCartJob::dispatchAfterResponse($cartData['laundryScheduleIds']);
        }

        foreach ($cartData['cart'] as $value) {
            $schedule = $value['schedule'];
            $this->sendNotif($schedule);
        }

        return response()->noContent();
    }

    public function sendNotif(Schedule $schedule)
    {
        // Send notification to worker
        $schedule->scheduleEmployees->each(function (ScheduleEmployee $scheduleEmployee) {
            scoped_localize($scheduleEmployee->user->info->language, function () use ($scheduleEmployee) {
                $displayDateTime = $scheduleEmployee->schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $scheduleEmployee->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::ScheduleUpdated(),
                            __('notification title schedule cleaning add product'),
                            __('notification body schedule cleaning add product', [
                                'worker' => $scheduleEmployee->user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'start_at' => $scheduleEmployee->schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    )
                );
            });
        });
    }

    /**
     * Get cart items (products and addons) for the given schedule with payment method handling.
     * For now only support for adding addons to the cart.
     *
     * @param  CreateCheckoutRequestDTO  $request
     * @return array{totalCredit: int, itemCount: int, cart: array, unavailableSchedules: array}
     */
    private function getCartItems($request): array
    {
        $totalCredit = 0;
        $itemCount = 0;
        $cart = [];
        $maxHourToAddItems = get_setting(GlobalSettingEnum::MaxProductAddTime(), 12);
        $unavailableSchedules = [];
        $cannotAcceptLaundryAddons = [];
        $schedules = $this->getSchedules($request);
        $scheduleCount = $schedules->count();
        $futureScheduleCount = $this->getFutureSchedulesCount();
        $addons = $this->getAddons($request);
        $laundryScheduleIds = [];

        /** @var \App\DTOs\Cart\CreateCartAddOnRequestDTO $addon */
        foreach ($request->addons as $addonRequest) {
            $schedule = $schedules->firstWhere('id', $addonRequest->schedule_id);
            $this->authorize('view', $schedule);

            /**
             * If the schedule is laundry and the addon is laundry,
             * or the schedule count is greater or equal to the future schedule count,
             * then schedule cannot accept the laundry addon.
             */
            if (($schedule && $schedule->isLaundry() &&
                $addonRequest->addon_id === config('downstairs.addons.laundry.id')) ||
                ($scheduleCount >= $futureScheduleCount)
            ) {
                $cannotAcceptLaundryAddons[] = [
                    'id' => $schedule->id,
                    'status' => $schedule->status,
                ];

                continue;
            } elseif ($schedule && $addonRequest->addon_id === config('downstairs.addons.laundry.id') &&
                ($scheduleCount <= $futureScheduleCount)) {
                // remove 1 because it will be added to the cart
                $scheduleCount--;
                // substract the future schedule count by 2 because we will use 2 schedule for laundry
                $futureScheduleCount -= 2;
                $laundryScheduleIds[] = $schedule->id;
            }

            if (! isset($cart[$addonRequest->schedule_id])) {
                if (! $schedule) {
                    continue;
                }

                $cart[$addonRequest->schedule_id]['schedule'] = $schedule;
                $cart[$addonRequest->schedule_id]['products'] = [];
                $cart[$addonRequest->schedule_id]['addons'] = [];
                $cart[$addonRequest->schedule_id]['transactions'] = [];
            } else {
                /** @var \App\Models\Schedule */
                $schedule = $cart[$addonRequest->schedule_id]['schedule'];
            }

            $addon = $addons->firstWhere('id', $addonRequest->addon_id);

            // Skip if the service is not in the add on services
            if (! $addon || ! $addon->services->contains($schedule->service_id)) {
                continue;
            }

            // Skip if the addon is already added
            if ($schedule->addons->contains('addon_id', $addon->id) ||
                collect($cart[$addonRequest->schedule_id]['addons'])->contains('addon_id', $addon->id)
            ) {
                continue;
            }

            // Add the schedule to the unavailable list if the schedule is near to start
            if ($schedule->start_at->diffInHours(now(), false) > (-$maxHourToAddItems)) {
                if (! in_array($schedule->id, array_column($unavailableSchedules, 'id'))) {
                    $unavailableSchedules[] = [
                        'id' => $schedule->id,
                        'status' => $schedule->status,
                    ];
                }

                continue;
            }

            $totalCredit += $addon->credit_price;
            $itemCount++;

            $isUseCredit = config('downstairs.addons.laundry.id') === $addon->id ?
                false : $request->use_credit;

            // Add to addons array
            $cart[$addonRequest->schedule_id]['addons'][] = $this->getCartAddon($addon, $isUseCredit, $addonRequest);

            // If using credit, add transaction
            if ($isUseCredit) {
                $cart[$addonRequest->schedule_id]['transactions'][] = [
                    'schedule_id' => $addonRequest->schedule_id,
                    'type' => CreditTransactionTypeEnum::Payment(),
                    'amount' => $addon->credit_price,
                    'description' => $addon->name,
                ];
            }
        }

        return [
            'totalCredit' => $totalCredit,
            'itemCount' => $itemCount,
            'cart' => $cart,
            'unavailableSchedules' => $unavailableSchedules,
            'cannotAcceptLaundryAddons' => $cannotAcceptLaundryAddons,
            'laundryScheduleIds' => $laundryScheduleIds,
        ];
    }

    /**
     * Get the data in order to add the schedule's items.
     *
     * @param  Addon  $addon
     * @param  bool  $useCredit
     * @param  CreateCartAddOnRequestDTO  $addonRequest
     */
    private function getCartAddon($addon, $useCredit, $addonRequest): array
    {
        return [
            'itemable_type' => Addon::class,
            'itemable_id' => $addon->id,
            'name' => $addon->name,
            'price' => config('downstairs.addons.laundry.id') === $addon->id ? 0 : $addon->price,
            'quantity' => $addonRequest->quantity,
            'discount_percentage' => $useCredit ? 100 : 0,
            'payment_method' => $useCredit ?
                CleaningProductPaymentMethodEnum::Credit() :
                CleaningProductPaymentMethodEnum::Invoice(),
        ];
    }

    /**
     * Get schedules from the request.
     *
     * @param  CreateCheckoutRequestDTO  $request
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule>
     */
    private function getSchedules($request)
    {
        $scheduleIds = collect($request->addons->toArray())
            ->pluck('schedule_id')
            ->unique()
            ->toArray();

        return Schedule::with(['scheduleEmployees.user.info', 'addons', 'service'])
            ->whereIn('id', $scheduleIds)
            ->booked()
            ->get();
    }

    /**
     * Get total future schedules that are not laundry order.
     *
     * @return int
     */
    private function getFutureSchedulesCount()
    {
        $userId = Auth::id();

        return Schedule::future()
            ->where('user_id', $userId)
            ->where('scheduleable_type', ScheduleCleaning::class)
            ->whereHas('scheduleable', function (Builder $query) {
                $query->whereNull('laundry_order_id');
            })
            ->count();
    }

    /**
     * Get addons from the request.
     *
     * @param  CreateCheckoutRequestDTO  $request
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Addon>
     */
    private function getAddons($request)
    {
        $addonIds = collect($request->addons->toArray())
            ->pluck('addon_id')
            ->unique()
            ->toArray();

        return Addon::with('services')->whereIn('id', $addonIds)->get();
    }

    /**
     * Validate the cart data.
     */
    private function validateCartData(array $cartData)
    {
        if (count($cartData['unavailableSchedules']) > 0) {
            throw new ErrorResponseException(
                __('unable to checkout some addons'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                errors: [
                    'unavailableSchedules' => $cartData['unavailableSchedules'],
                ]
            );
        } elseif (count($cartData['cannotAcceptLaundryAddons']) > 0) {
            throw new ErrorResponseException(
                __('unable to add laundry addons to schedule'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                errors: [
                    'cannotAcceptLaundryAddons' => $cartData['cannotAcceptLaundryAddons'],
                ]
            );
        } elseif ($cartData['itemCount'] === 0) {
            throw new ErrorResponseException(
                __('no items added'),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
