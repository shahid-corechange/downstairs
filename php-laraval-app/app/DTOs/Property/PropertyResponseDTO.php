<?php

namespace App\DTOs\Property;

use App\DTOs\Address\AddressResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Property;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class PropertyResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $addressId,
        public Lazy|null|int $typeId,
        public Lazy|null|string $membershipType,
        public Lazy|null|float $squareMeter,
        public Lazy|null|KeyInformationResponseDTO $keyInformation,
        public Lazy|null|string $keyDescription,
        public Lazy|null|string $keyPlace,
        public Lazy|null|float $status,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|AddressResponseDTO $address,
        public Lazy|null|PropertyTypeResponseDTO $type,
        public Lazy|null|UserResponseDTO $companyUser,
        #[DataCollectionOf(UserResponseDTO::class)]
        public Lazy|null|DataCollection $users,
        public Lazy|null|array $meta,
    ) {
    }

    public static function fromModel(Property $property): self
    {
        return new self(
            Lazy::create(fn () => $property->id)->defaultIncluded(),
            Lazy::create(fn () => $property->address_id)->defaultIncluded(),
            Lazy::create(fn () => $property->property_type_id)->defaultIncluded(),
            Lazy::create(fn () => $property->membership_type)->defaultIncluded(),
            Lazy::create(fn () => $property->square_meter)->defaultIncluded(),
            Lazy::create(fn () => $property->key_information ?
                KeyInformationResponseDTO::from($property->key_information) : null)->defaultIncluded(),
            Lazy::create(fn () => $property->key_description)->defaultIncluded(),
            Lazy::create(fn () => $property->key_place)->defaultIncluded(),
            Lazy::create(fn () => $property->status)->defaultIncluded(),
            Lazy::create(fn () => $property->created_at)->defaultIncluded(),
            Lazy::create(fn () => $property->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $property->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => AddressResponseDTO::from($property->address)),
            Lazy::create(fn () => PropertyTypeResponseDTO::from($property->type)),
            Lazy::create(fn () => $property->companyUser->isNotEmpty() ?
                UserResponseDTO::from($property->companyUser[0]) : null),
            Lazy::create(fn () => UserResponseDTO::collection($property->users)),
            Lazy::create(fn () => static::getModelMeta($property))->defaultIncluded(),
        );
    }
}
