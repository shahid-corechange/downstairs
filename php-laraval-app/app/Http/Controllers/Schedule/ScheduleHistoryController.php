<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\Schedule\ScheduleHistoryCreateRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Enums\VatNumbersEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SendNotificationJob;
use App\Jobs\SentWorkingHoursJob;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Addon;
use App\Models\FixedPrice;
use App\Models\Product;
use App\Models\Property;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\SubscriptionCleaningDetail;
use App\Models\Team;
use App\Services\Order\OrderCleaningService;
use App\Services\Subscription\SubscriptionService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ScheduleHistoryController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'customer',
        'property.address.city',
        'user',
        'team',
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
    ];

    public function __construct(
        protected OrderCleaningService $orderService
    ) {
    }

    /**
     * Create schedule history.
     * For now, it's only support for cleaning.
     */
    public function store(
        ScheduleHistoryCreateRequestDTO $request,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        $isHistorical = $request->isNotOptional('workers');
        $teamMembers = Team::find($request->team_id)->users;
        $quarters = $request->quarters;

        $endTimeAt = calculate_end_time(
            $request->start_time_at,
            calculate_calendar_quarters(
                $quarters,
                $isHistorical ? $request->workers->count() : $teamMembers->count()
            )
        );

        $carbonEndAt = Carbon::createFromDate($request->start_at)->addDays(
            $request->start_time_at > $endTimeAt ? 1 : 0
        );
        $endAt = $carbonEndAt->format('Y-m-d');

        $collidedSchedules = $subscriptionService->checkCollision(
            $request->team_id,
            0,
            $request->start_at,
            $request->start_time_at,
            $endAt,
            $endTimeAt,
        );

        if ($collidedSchedules->isNotEmpty()) {
            return $this->errorResponse(
                __('schedules collisions'),
                Response::HTTP_BAD_REQUEST,
                errors: [
                    'collisions' => ScheduleResponseDTO::transformCollection(
                        $collidedSchedules,
                        includes: ['user', 'team'],
                        onlys: [
                            'id',
                            'user.fullname',
                            'team.name',
                            'startAt',
                            'endAt',
                        ]
                    ),
                ]
            );
        }

        /** @var Schedule $schedule */
        $schedule = DB::transaction(
            function () use (
                $request,
                $quarters,
                $endAt,
                $endTimeAt,
                $isHistorical,
            ): Schedule {
                $detail = SubscriptionCleaningDetail::create([
                    'property_id' => $request->property_id,
                    'team_id' => $request->team_id,
                    'quarters' => $quarters,
                    'start_time' => $request->start_time_at,
                    'end_time' => $endTimeAt,
                ]);

                $subscription = Subscription::create([
                    ...$request->toArray(),
                    'end_at' => $endAt,
                    'end_time_at' => $endTimeAt,
                    'frequency' => SubscriptionFrequencyEnum::Once(),
                    'refill_sequence' => 1,
                    'fixed_price_id' => null,
                    'status' => SubscriptionStatusEnum::Active(),
                    'subscribable_type' => SubscriptionCleaningDetail::class,
                    'subscribable_id' => $detail->id,
                ]);

                if ($request->isNotOptional('products')) {
                    $products = products_request_to_array($request->products->toArray());
                    $subscription->products()->sync($products);
                }

                if ($request->isNotOptional('addon_ids')) {
                    $subscription->addons()->sync($request->addon_ids);
                }

                if ($request->isNotOptional('total_price')) {
                    $this->createFixedPrice($request, $subscription);
                }

                $schedule = $this->createSchedule($request, $subscription, $isHistorical);

                if ($isHistorical) {
                    scoped_localize('sv_SE', function () use ($schedule) {
                        [$order, $invoice] = $this->orderService->createOrder($schedule);
                        $this->orderService->createOrderRows($order, $schedule);
                        UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
                    });
                }

                return $schedule;
            }
        );

        if ($isHistorical) {
            // Create attendance for workers
            foreach ($schedule->scheduleEmployees as $employee) {
                SentWorkingHoursJob::dispatchAfterResponse($employee);
            }
        } else {
            $this->sendCreatedNotifications($schedule);
        }

        app()->setLocale(Auth::user()->info->language);

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            Response::HTTP_CREATED,
            message: __('schedule history created successfully')
        );
    }

    /**
     * Create fixed price for subscription.
     */
    private function createFixedPrice(
        ScheduleHistoryCreateRequestDTO $request,
        Subscription $subscription,
    ) {
        $vat = VatNumbersEnum::TwentyFive();
        $price = $request->total_price / (1 + $vat / 100);
        /**
         * Get fixed price where fixed price row type is service
         * and price is equal to new price
         */
        $fixedPrice = FixedPrice::withTrashed()
            ->where('user_id', $request->user_id)
            ->where('is_per_order', true)
            ->whereHas('rows', function ($query) use ($price) {
                $query->where('type', FixedPriceRowTypeEnum::Service())
                    ->where('price', $price)
                    ->where('has_rut', true);
            })
            ->first();

        // If fixed price not found, create new fixed price
        if (! $fixedPrice) {
            $fixedPrice = FixedPrice::create([
                'user_id' => $request->user_id,
                'is_per_order' => true,
            ]);

            $fixedPrice->rows()->create([
                'type' => FixedPriceRowTypeEnum::Service(),
                'quantity' => 1,
                'price' => $price,
                'vat_group' => $vat,
                'has_rut' => $subscription->customer->membership_type === MembershipTypeEnum::Private(),
            ]);
        }

        $subscription->update([
            'fixed_price_id' => $fixedPrice->id,
        ]);

        // If fixed price is trashed or soft delete, restore it
        if ($fixedPrice->trashed()) {
            $fixedPrice->restore();
        }
    }

    /**
     * Create schedule for subscription.
     */
    private function createSchedule(
        ScheduleHistoryCreateRequestDTO $request,
        Subscription $subscription,
        bool $isHistorical
    ): Schedule {
        $start_at = $subscription->start_at->setTimeFromTimeString($subscription->subscribable->start_time);
        $end_at = $subscription->end_at->setTimeFromTimeString($subscription->subscribable->end_time);
        /** @var Property|null $property */
        $property = $subscription->isCleaning() ? $subscription->subscribable->property :
            $subscription->subscribable->pickupProperty;
        $scheduleNote = array_filter([
            'subscription_note' => $subscription->description,
            'property_note' => $property?->getMeta('note'),
        ]);

        $scheduleCleaning = ScheduleCleaning::create([]);

        // Create the schedule
        $schedule = Schedule::create([
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'service_id' => $subscription->service_id,
            'team_id' => $request->team_id,
            'customer_id' => $subscription->customer_id,
            'property_id' => $property?->id,
            'status' => $isHistorical ? ScheduleStatusEnum::Done() : ScheduleStatusEnum::Booked(),
            'start_at' => $start_at->toDateTimeString(),
            'end_at' => $end_at->toDateTimeString(),
            'quarters' => $request->quarters,
            'key_information' => $property?->key_description,
            'note' => empty($scheduleNote) ? ['note' => ''] : $scheduleNote,
            'is_fixed' => false,
            'scheduleable_id' => $scheduleCleaning->id,
            'scheduleable_type' => ScheduleCleaning::class,
        ]);
        $lang = $schedule->subscription->user->info->language;

        if ($lang) {
            app()->setLocale($lang);
        }

        $products = $schedule->subscription->products;

        // Create the schedule employees
        if ($isHistorical) {
            foreach ($request->workers->toArray() as $worker) {
                $schedule->scheduleEmployees()->create([
                    'user_id' => $worker['user_id'],
                    'start_at' => $worker['start_at'],
                    'end_at' => $worker['end_at'],
                    'status' => ScheduleStatusEnum::Done(),
                ]);
            }
        } else {
            $team = $subscription->isCleaning() ? $subscription->subscribable->team :
                $subscription->subscribable->pickupTeam;

            foreach ($team->users as $worker) {
                $schedule->scheduleEmployees()->create([
                    'user_id' => $worker->id,
                ]);
            }
        }

        // Create the schedule products
        if ($products) {
            $products = $products
                ->map(fn ($product) => [
                    'itemable_id' => $product->product_id,
                    'itemable_type' => Product::class,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'discount_percentage' => 0,
                ])
                ->toArray();
            $schedule->items()->createMany($products);
        }

        // Create the schedule addons
        if ($subscription->addons) {
            $addons = $subscription->addons
                ->map(fn ($addon) => [
                    'itemable_id' => $addon->id,
                    'itemable_type' => Addon::class,
                    'price' => $addon->price,
                ])
                ->toArray();
            $schedule->items()->createMany($addons);
        }

        return $schedule;
    }

    private function sendCreatedNotifications(Schedule $schedule)
    {

        // send notification to customer
        scoped_localize($schedule->user->info->language, function () use ($schedule) {
            $startAt = $schedule->start_at
                ->copy()
                ->timezone('Europe/Stockholm');
            $endAt = $schedule->end_at
                ->copy()
                ->timezone('Europe/Stockholm');

            SendNotificationJob::dispatchAfterResponse(
                $schedule->user,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Customer(),
                        NotificationTypeEnum::SubscriptionAdded(),
                        __('notification title subscription created'),
                        __('notification body subscription created', [
                            'customer' => $schedule->user->first_name,
                            'service' => $schedule->service->name,
                            'start_at' => $startAt->format('Y-m-d'),
                            'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
                        ]),
                    ),
                    shouldSave: true,
                ),
            );
        });

        // send notification to employee
        $schedule->scheduleEmployees->each(
            function ($employee) {
                scoped_localize($employee->user->info->language, function () use ($employee) {
                    SendNotificationJob::dispatchAfterResponse(
                        $employee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::ScheduleAdded(),
                                __('notification title schedule added'),
                                __('notification body some schedule added', [
                                    'worker' => $employee->user->first_name,
                                ]),
                            ),
                            shouldSave: true,
                        ),
                    );
                });
            }
        );
    }
}
