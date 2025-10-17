<?php

namespace App\DTOs\LaundryOrderHistory;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\LaundryOrderHistory;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LaundryOrderHistoryResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $laundryOrderId,
        public Lazy|null|int $causerId,
        public Lazy|null|string $type,
        public Lazy|null|string $note,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|LaundryOrderResponseDTO $laundryOrder,
        public Lazy|null|UserResponseDTO $causer,
    ) {
    }

    public static function fromModel(LaundryOrderHistory $laundryOrderHistory): self
    {

        return new self(
            Lazy::create(fn () => $laundryOrderHistory->id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderHistory->laundry_order_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderHistory->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderHistory->type)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderHistory->note)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderHistory->created_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderHistory->updated_at)->defaultIncluded(),
            Lazy::create(fn () => LaundryOrderResponseDTO::from($laundryOrderHistory->laundryOrder)),
            Lazy::create(fn () => UserResponseDTO::from($laundryOrderHistory->causer)),
        );
    }
}
