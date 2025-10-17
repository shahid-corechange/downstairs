<?php

namespace App\DTOs\StoreSale;

use App\DTOs\BaseData;
use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\StoreSale;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class StoreSaleResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $storeId,
        public Lazy|null|int $causerId,
        public Lazy|null|string $status,
        public Lazy|null|string $paymentMethod,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|float $totalPriceWithVat,
        public Lazy|null|float $totalPriceWithDiscount,
        public Lazy|null|float $totalDiscount,
        public Lazy|null|Collection $totalVat,
        public Lazy|null|float $totalToPay,
        public Lazy|null|float $roundedTotalToPay,
        public Lazy|null|float $roundAmount,
        public Lazy|null|StoreResponseDTO $store,
        public Lazy|null|UserResponseDTO $causer,
        #[DataCollectionOf(StoreSaleProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
    ) {
    }

    public static function fromModel(StoreSale $storeSale): self
    {
        return new self(
            Lazy::create(fn () => $storeSale->id)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->store_id)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->status)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->payment_method)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->created_at)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->total_price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->total_price_with_discount)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->total_discount)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->total_vat)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->total_to_pay)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->rounded_total_to_pay)->defaultIncluded(),
            Lazy::create(fn () => $storeSale->round_amount)->defaultIncluded(),
            Lazy::create(fn () => StoreResponseDTO::from($storeSale->store)),
            Lazy::create(fn () => UserResponseDTO::from($storeSale->causer)),
            Lazy::create(fn () => StoreSaleProductResponseDTO::collection($storeSale->products)),
        );
    }
}
