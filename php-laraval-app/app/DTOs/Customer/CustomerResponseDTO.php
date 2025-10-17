<?php

namespace App\DTOs\Customer;

use App\DTOs\Address\AddressResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Customer;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CustomerResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $addressId,
        public Lazy|null|int $customerRefId,
        public Lazy|null|string $fortnoxId,
        public Lazy|null|string $membershipType,
        public Lazy|null|string $type,
        public Lazy|null|string $reference,
        public Lazy|null|string $identityNumber,
        public Lazy|null|string $name,
        public Lazy|null|string $email,
        public Lazy|null|string $phone1,
        public Lazy|null|string $dialCode,
        public Lazy|null|int $dueDays,
        public Lazy|null|int $invoiceMethod,
        public Lazy|null|string $formattedPhone1,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|AddressResponseDTO $address,
        public Lazy|null|CustomerResponseDTO $customerRef,
        public Lazy|null|UserResponseDTO $companyUser,
        #[DataCollectionOf(UserResponseDTO::class)]
        public Lazy|null|DataCollection $companyContactUsers,
        #[DataCollectionOf(UserResponseDTO::class)]
        public Lazy|null|DataCollection $users,
        public Lazy|null|array $meta
    ) {
    }

    public static function fromModel(Customer $customer): self
    {
        return new self(
            Lazy::create(fn () => $customer->id)->defaultIncluded(),
            Lazy::create(fn () => $customer->address_id)->defaultIncluded(),
            Lazy::create(fn () => $customer->customer_ref_id)->defaultIncluded(),
            Lazy::create(fn () => $customer->fortnox_id)->defaultIncluded(),
            Lazy::create(fn () => $customer->membership_type)->defaultIncluded(),
            Lazy::create(fn () => $customer->type)->defaultIncluded(),
            Lazy::create(fn () => $customer->reference)->defaultIncluded(),
            Lazy::create(fn () => $customer->identity_number)->defaultIncluded(),
            Lazy::create(fn () => $customer->name)->defaultIncluded(),
            Lazy::create(fn () => $customer->email)->defaultIncluded(),
            Lazy::create(fn () => $customer->phone1)->defaultIncluded(),
            Lazy::create(fn () => $customer->dial_code)->defaultIncluded(),
            Lazy::create(fn () => $customer->due_days)->defaultIncluded(),
            Lazy::create(fn () => $customer->invoice_method)->defaultIncluded(),
            Lazy::create(fn () => $customer->formatted_phone1)->defaultIncluded(),
            Lazy::create(fn () => $customer->created_at)->defaultIncluded(),
            Lazy::create(fn () => $customer->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $customer->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $customer->address ? AddressResponseDTO::from($customer->address) : null),
            Lazy::create(fn () => $customer->customerRef ? static::from($customer->customerRef) : null),
            Lazy::create(fn () => $customer->companyUser ? UserResponseDTO::from($customer->companyUser) : null),
            Lazy::create(fn () => UserResponseDTO::collection($customer->company_contact_users)),
            Lazy::create(fn () => UserResponseDTO::collection($customer->users()->withTrashed()->get())),
            Lazy::create(fn () => static::getModelMeta($customer))->defaultIncluded()
        );
    }
}
