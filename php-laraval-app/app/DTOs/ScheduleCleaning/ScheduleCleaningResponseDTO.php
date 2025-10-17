<?php

namespace App\DTOs\ScheduleCleaning;

use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\ScheduleCleaningProduct\ScheduleCleaningProductResponseDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Customer;
use App\Models\ScheduleCleaning;
use App\Models\Team;
use App\Models\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleCleaningResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $subscriptionId,
        public Lazy|null|int $teamId,
        public Lazy|null|int $customerId,
        public Lazy|null|int $propertyId,
        public Lazy|null|int $laundryOrderId,
        public Lazy|null|string $laundryType,
        public Lazy|null|string $status,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endAt,
        public Lazy|null|int $quarters,
        public Lazy|null|bool $isFixed,
        public Lazy|null|string $keyInformation,
        public Lazy|null|ScheduleCleaningNoteResponseDTO $notes,
        public Lazy|null|string $note,
        public Lazy|null|int $cancelableId,
        public Lazy|null|string $canceledBy,
        public Lazy|null|string $canceledType,
        public Lazy|null|string $actualStartAt,
        public Lazy|null|string $actualEndAt,
        public Lazy|null|string $actualQuarters,
        public Lazy|null|bool $hasDeviation,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|string $canceledAt,
        public Lazy|null|SubscriptionResponseDTO $subscription,
        public Lazy|null|TeamResponseDTO $team,
        public Lazy|null|CustomerResponseDTO $customer,
        public Lazy|null|PropertyResponseDTO $property,
        public Lazy|null|LaundryOrderResponseDTO $laundryOrder,
        public Lazy|null|ScheduleCleaningRefundResponseDTO $refund,
        #[DataCollectionOf(ScheduleCleaningProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        #[DataCollectionOf(ScheduleEmployeeResponseDTO::class)]
        public Lazy|null|DataCollection $allEmployees,
        #[DataCollectionOf(ScheduleEmployeeResponseDTO::class)]
        public Lazy|null|DataCollection $activeEmployees,
        #[DataCollectionOf(ScheduleCleaningTaskResponseDTO::class)]
        public Lazy|null|DataCollection $scheduleCleaningTasks,
        #[DataCollectionOf(ProductSummaryResponseDTO::class)]
        public Lazy|null|DataCollection $productSummaries,
        public Lazy|null|BaseData $cancelable,
    ) {
    }

    public static function fromModel(ScheduleCleaning $scheduleCleaning): self
    {
        return new self(
            Lazy::create(fn () => $scheduleCleaning->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->subscription_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->team_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->property_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->laundry_order_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->laundry_type)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->status)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->start_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->end_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->quarters)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->is_fixed)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->key_information)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->note ?
                ScheduleCleaningNoteResponseDTO::from($scheduleCleaning->note) : null)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->full_note)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->cancelable_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->canceled_by)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->canceled_type)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->actual_start_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->actual_end_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->actual_quarters)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->has_deviation)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->created_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->canceled_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->subscription ?
                SubscriptionResponseDTO::from($scheduleCleaning->subscription) :
                null),
            Lazy::create(fn () => $scheduleCleaning->team ?
                TeamResponseDTO::from($scheduleCleaning->team) :
                null),
            Lazy::create(fn () => $scheduleCleaning->customer ?
                CustomerResponseDTO::from($scheduleCleaning->customer) :
                null),
            Lazy::create(fn () => $scheduleCleaning->property ?
                PropertyResponseDTO::from($scheduleCleaning->property) :
                null),
            Lazy::create(fn () => $scheduleCleaning->laundryOrder ?
                LaundryOrderResponseDTO::from($scheduleCleaning->laundryOrder) :
                null),
            Lazy::create(fn () => $scheduleCleaning->refund ?
                ScheduleCleaningRefundResponseDTO::from($scheduleCleaning->refund) :
                null),
            Lazy::create(fn () => ScheduleCleaningProductResponseDTO::collection($scheduleCleaning->products)),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($scheduleCleaning->tasks)),
            Lazy::create(fn () => ScheduleEmployeeResponseDTO::collection($scheduleCleaning->allEmployees)),
            Lazy::create(fn () => ScheduleEmployeeResponseDTO::collection($scheduleCleaning->activeEmployees)),
            Lazy::create(fn () => ScheduleCleaningTaskResponseDTO::collection(
                $scheduleCleaning->scheduleCleaningTasks
            )),
            Lazy::create(fn () => ProductSummaryResponseDTO::collection(
                $scheduleCleaning->productSummaries()
            )),
            Lazy::create(fn () => self::getCancelable($scheduleCleaning)),
        );
    }

    private static function getCancelable(ScheduleCleaning $scheduleCleaning): ?BaseData
    {
        if ($scheduleCleaning->cancelable_type === User::class) {
            return UserResponseDTO::from($scheduleCleaning->cancelable);
        } elseif ($scheduleCleaning->cancelable_type === Customer::class) {
            return CustomerResponseDTO::from($scheduleCleaning->cancelable);
        } elseif ($scheduleCleaning->cancelable_type === Team::class) {
            return TeamResponseDTO::from($scheduleCleaning->cancelable);
        }

        return null;
    }
}
