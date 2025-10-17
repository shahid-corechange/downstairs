<?php

namespace App\DTOs\Employee;

use App\DTOs\Address\AddressResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Employee;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class EmployeeResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $addressId,
        public Lazy|null|int $userId,
        public Lazy|null|string $fortnoxId,
        public Lazy|null|string $identityNumber,
        public Lazy|null|string $name,
        public Lazy|null|string $email,
        public Lazy|null|string $phone1,
        public Lazy|null|string $dialCode,
        public Lazy|null|string $formattedPhone1,
        public Lazy|null|bool $isValidIdentity,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|AddressResponseDTO $address,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|array $meta
    ) {
    }

    public static function fromModel(Employee $employee): self
    {
        return new self(
            Lazy::create(fn () => $employee->id)->defaultIncluded(),
            Lazy::create(fn () => $employee->address_id)->defaultIncluded(),
            Lazy::create(fn () => $employee->user_id)->defaultIncluded(),
            Lazy::create(fn () => $employee->fortnox_id)->defaultIncluded(),
            Lazy::create(fn () => $employee->identity_number)->defaultIncluded(),
            Lazy::create(fn () => $employee->name)->defaultIncluded(),
            Lazy::create(fn () => $employee->email)->defaultIncluded(),
            Lazy::create(fn () => $employee->phone1)->defaultIncluded(),
            Lazy::create(fn () => $employee->dial_code)->defaultIncluded(),
            Lazy::create(fn () => $employee->formatted_phone1)->defaultIncluded(),
            Lazy::create(fn () => $employee->is_valid_identity)->defaultIncluded(),
            Lazy::create(fn () => $employee->created_at)->defaultIncluded(),
            Lazy::create(fn () => $employee->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $employee->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => AddressResponseDTO::from($employee->address)),
            Lazy::create(fn () => UserResponseDTO::from($employee->user)),
            Lazy::create(fn () => static::getModelMeta($employee))->defaultIncluded()
        );
    }
}
