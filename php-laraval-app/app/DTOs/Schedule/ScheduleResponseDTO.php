<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\ScheduleCleaning\ScheduleCleaningResponseDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\ScheduleItem\ItemSummaryResponseDTO;
use App\DTOs\ScheduleItem\ScheduleItemResponseDTO;
use App\DTOs\ScheduleLaundry\ScheduleLaundryResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Customer;
use App\Models\Schedule;
use App\Models\Team;
use App\Models\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $serviceId,
        public Lazy|null|int $teamId,
        public Lazy|null|int $customerId,
        public Lazy|null|int $propertyId,
        public Lazy|null|int $subscriptionId,
        public Lazy|null|string $scheduleableType,
        public Lazy|null|string $scheduleableId,
        public Lazy|null|string $status,
        public Lazy|null|string $workStatus,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endAt,
        public Lazy|null|string $originalStartAt,
        public Lazy|null|int $quarters,
        public Lazy|null|bool $isFixed,
        public Lazy|null|string $keyInformation,
        public Lazy|null|string $note,
        public Lazy|null|ScheduleNoteResponseDTO $notes,
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
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|ServiceResponseDTO $service,
        public Lazy|null|TeamResponseDTO $team,
        public Lazy|null|CustomerResponseDTO $customer,
        public Lazy|null|PropertyResponseDTO $property,
        public Lazy|null|SubscriptionResponseDTO $subscription,
        public Lazy|null|ScheduleRefundResponseDTO $refund,
        // TODO: add products and addons fields directly here to avoid redundant queries through items
        #[DataCollectionOf(ScheduleItemResponseDTO::class)]
        public Lazy|null|DataCollection $items,
        public Lazy|null|ScheduleChangeResponseDTO $changeRequest,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        #[DataCollectionOf(ScheduleEmployeeResponseDTO::class)]
        public Lazy|null|DataCollection $allEmployees,
        #[DataCollectionOf(ScheduleEmployeeResponseDTO::class)]
        public Lazy|null|DataCollection $activeEmployees,
        #[DataCollectionOf(ScheduleTaskResponseDTO::class)]
        public Lazy|null|DataCollection $scheduleTasks,
        #[DataCollectionOf(ItemSummaryResponseDTO::class)]
        public Lazy|null|DataCollection $addonSummaries,
        public Lazy|null|BaseData $cancelable,
        public Lazy|null|BaseData $detail,
    ) {
    }

    public static function fromModel(Schedule $schedule): self
    {
        return new self(
            Lazy::create(fn () => $schedule->id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->user_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->service_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->team_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->property_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->subscription_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->scheduleable_type)->defaultIncluded(),
            Lazy::create(fn () => $schedule->scheduleable_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->status)->defaultIncluded(),
            Lazy::create(fn () => $schedule->work_status)->defaultIncluded(),
            Lazy::create(fn () => $schedule->start_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->end_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->original_start_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->quarters)->defaultIncluded(),
            Lazy::create(fn () => $schedule->is_fixed)->defaultIncluded(),
            Lazy::create(fn () => $schedule->key_information)->defaultIncluded(),
            Lazy::create(fn () => $schedule->full_note)->defaultIncluded(),
            Lazy::create(fn () => $schedule->note ?
                ScheduleNoteResponseDTO::from($schedule->note) : null)->defaultIncluded(),
            Lazy::create(fn () => $schedule->cancelable_id)->defaultIncluded(),
            Lazy::create(fn () => $schedule->canceled_by)->defaultIncluded(),
            Lazy::create(fn () => $schedule->canceled_type)->defaultIncluded(),
            Lazy::create(fn () => $schedule->actual_start_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->actual_end_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->actual_quarters)->defaultIncluded(),
            Lazy::create(fn () => $schedule->has_deviation)->defaultIncluded(),
            Lazy::create(fn () => $schedule->created_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $schedule->canceled_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($schedule->user)),
            Lazy::create(fn () => ServiceResponseDTO::from($schedule->service)),
            Lazy::create(fn () => TeamResponseDTO::from($schedule->team)),
            Lazy::create(fn () => $schedule->customer ?
                CustomerResponseDTO::from($schedule->customer) : null),
            Lazy::create(fn () => PropertyResponseDTO::from($schedule->property)),
            Lazy::create(fn () => $schedule->subscription ?
                SubscriptionResponseDTO::from($schedule->subscription) :
                null),
            Lazy::create(fn () => $schedule->refund ?
                ScheduleRefundResponseDTO::from($schedule->refund) :
                null),
            Lazy::create(fn () => ScheduleItemResponseDTO::collection($schedule->items)),
            Lazy::create(fn () => $schedule->changeRequest ?
                ScheduleChangeResponseDTO::from($schedule->changeRequest) :
                null),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($schedule->tasks)),
            Lazy::create(fn () => ScheduleEmployeeResponseDTO::collection($schedule->allEmployees)),
            Lazy::create(fn () => ScheduleEmployeeResponseDTO::collection($schedule->activeEmployees)),
            Lazy::create(fn () => ScheduleTaskResponseDTO::collection(
                $schedule->scheduleTasks
            )),
            Lazy::create(fn () => ItemSummaryResponseDTO::collection(
                $schedule->addonSummaries()
            )),
            Lazy::create(fn () => self::getCancelable($schedule)),
            Lazy::create(fn () => $schedule->isCleaning() ?
                ScheduleCleaningResponseDTO::from($schedule->scheduleable) :
                ScheduleLaundryResponseDTO::from($schedule->scheduleable)),
        );
    }

    private static function getCancelable(Schedule $schedule): ?BaseData
    {
        if ($schedule->cancelable_type === User::class) {
            return UserResponseDTO::from($schedule->cancelable);
        } elseif ($schedule->cancelable_type === Customer::class) {
            return CustomerResponseDTO::from($schedule->cancelable);
        } elseif ($schedule->cancelable_type === Team::class) {
            return TeamResponseDTO::from($schedule->cancelable);
        }

        return null;
    }
}
