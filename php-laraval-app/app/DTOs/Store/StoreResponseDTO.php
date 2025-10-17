<?php

namespace App\DTOs\Store;

use App\DTOs\Address\AddressResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\StoreProduct\StoreProductResponseDTO;
use App\DTOs\StoreSale\StoreSaleResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Store;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class StoreResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $addressId,
        public Lazy|null|string $name,
        public Lazy|null|string $companyNumber,
        public Lazy|null|string $phone,
        public Lazy|null|string $dialCode,
        public Lazy|null|string $email,
        public Lazy|null|string $formattedPhone,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|AddressResponseDTO $address,
        #[DataCollectionOf(StoreProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(UserResponseDTO::class)]
        public Lazy|null|DataCollection $users,
        #[DataCollectionOf(LaundryOrderResponseDTO::class)]
        public Lazy|null|DataCollection $laundryOrders,
        #[DataCollectionOf(StoreSaleResponseDTO::class)]
        public Lazy|null|DataCollection $sales,
    ) {
    }

    public static function fromModel(Store $store): self
    {
        return new self(
            Lazy::create(fn () => $store->id)->defaultIncluded(),
            Lazy::create(fn () => $store->address_id)->defaultIncluded(),
            Lazy::create(fn () => $store->name)->defaultIncluded(),
            Lazy::create(fn () => $store->company_number)->defaultIncluded(),
            Lazy::create(fn () => $store->phone)->defaultIncluded(),
            Lazy::create(fn () => $store->dial_code)->defaultIncluded(),
            Lazy::create(fn () => $store->email)->defaultIncluded(),
            Lazy::create(fn () => $store->formatted_phone)->defaultIncluded(),
            Lazy::create(fn () => $store->created_at)->defaultIncluded(),
            Lazy::create(fn () => $store->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $store->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => AddressResponseDTO::from($store->address)),
            Lazy::create(fn () => StoreProductResponseDTO::collection($store->products)),
            Lazy::create(fn () => UserResponseDTO::collection($store->users)),
            Lazy::create(fn () => LaundryOrderResponseDTO::collection($store->laundryOrders)),
            Lazy::create(fn () => StoreSaleResponseDTO::collection($store->sales)),
        );
    }
}
