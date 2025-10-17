<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\CustomerDiscount\CustomerDiscountResponseDTO;
use App\DTOs\Employee\EmployeeResponseDTO;
use App\DTOs\Log\AuthLogResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\RutCoApplicant\RutCoApplicantResponseDTO;
use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\Models\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UserResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $firstName,
        public Lazy|null|string $lastName,
        public Lazy|null|string $email,
        public Lazy|null|string $emailVerifiedAt,
        public Lazy|null|string $cellphone,
        public Lazy|null|string $dialCode,
        public Lazy|null|string $formattedCellphone,
        public Lazy|null|string $cellphoneVerifiedAt,
        public Lazy|null|string $identityNumber,
        public Lazy|null|string $identityNumberVerifiedAt,
        public Lazy|null|string $lastSeen,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|string $fullname,
        public Lazy|null|string $initials,
        public Lazy|null|string $status,
        public Lazy|null|array $permissions,
        public Lazy|null|int $totalCredits,
        public Lazy|null|UserInfoResponseDTO $info,
        public Lazy|null|EmployeeResponseDTO $employee,
        #[DataCollectionOf(TeamResponseDTO::class)]
        public Lazy|null|DataCollection $teams,
        #[DataCollectionOf(PropertyResponseDTO::class)]
        public Lazy|null|DataCollection $properties,
        #[DataCollectionOf(CustomerResponseDTO::class)]
        public Lazy|null|DataCollection $customers,
        #[DataCollectionOf(AuthLogResponseDTO::class)]
        public Lazy|null|DataCollection $authentications,
        #[DataCollectionOf(UserRoleResponseDTO::class)]
        public Lazy|null|DataCollection $roles,
        #[DataCollectionOf(SubscriptionResponseDTO::class)]
        public Lazy|null|DataCollection $subscriptions,
        #[DataCollectionOf(RutCoApplicantResponseDTO::class)]
        public Lazy|null|DataCollection $rutCoApplicants,
        #[DataCollectionOf(CustomerDiscountResponseDTO::class)]
        public Lazy|null|DataCollection $laundryDiscounts,
        #[DataCollectionOf(CustomerDiscountResponseDTO::class)]
        public Lazy|null|DataCollection $cleaningDiscounts,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            Lazy::create(fn () => $user->id)->defaultIncluded(),
            Lazy::create(fn () => $user->first_name)->defaultIncluded(),
            Lazy::create(fn () => $user->last_name)->defaultIncluded(),
            Lazy::create(fn () => $user->email)->defaultIncluded(),
            Lazy::create(fn () => $user->email_verified_at)->defaultIncluded(),
            Lazy::create(fn () => $user->cellphone)->defaultIncluded(),
            Lazy::create(fn () => $user->dial_code)->defaultIncluded(),
            Lazy::create(fn () => $user->formatted_cellphone)->defaultIncluded(),
            Lazy::create(fn () => $user->cellphone_verified_at)->defaultIncluded(),
            Lazy::create(fn () => $user->identity_number)->defaultIncluded(),
            Lazy::create(fn () => $user->identity_number_verified_at)->defaultIncluded(),
            Lazy::create(fn () => $user->last_seen)->defaultIncluded(),
            Lazy::create(fn () => $user->created_at)->defaultIncluded(),
            Lazy::create(fn () => $user->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $user->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $user->fullname)->defaultIncluded(),
            Lazy::create(fn () => $user->initials)->defaultIncluded(),
            Lazy::create(fn () => $user->status)->defaultIncluded(),
            Lazy::create(fn () => $user->getAllPermissions()->map(fn ($item) => $item->name)),
            Lazy::create(fn () => $user->total_credits)->defaultIncluded(),
            Lazy::create(fn () => UserInfoResponseDTO::from($user->info)),
            Lazy::create(fn () => $user->employee ? EmployeeResponseDTO::from($user->employee) : null),
            Lazy::create(fn () => TeamResponseDTO::collection($user->teams)),
            Lazy::create(fn () => PropertyResponseDTO::collection($user->properties)),
            Lazy::create(fn () => CustomerResponseDTO::collection($user->customers)),
            Lazy::create(fn () => AuthLogResponseDTO::collection($user->authentications)),
            Lazy::create(fn () => UserRoleResponseDTO::collection($user->roles)),
            Lazy::create(fn () => SubscriptionResponseDTO::collection($user->subscriptions)),
            Lazy::create(fn () => RutCoApplicantResponseDTO::collection($user->rutCoApplicants)),
            Lazy::create(fn () => CustomerDiscountResponseDTO::collection($user->laundryDiscounts)),
            Lazy::create(fn () => CustomerDiscountResponseDTO::collection($user->cleaningDiscounts)),
        );
    }
}
