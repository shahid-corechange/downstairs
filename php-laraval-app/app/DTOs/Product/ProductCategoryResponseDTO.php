<?php

namespace App\DTOs\Product;

use App\DTOs\BaseData;
use App\Models\ProductCategory;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ProductCategoryResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
    ) {
    }

    public static function fromModel(ProductCategory $product): self
    {
        return new self(
            Lazy::create(fn () => $product->id)->defaultIncluded(),
            Lazy::create(fn () => $product->name)->defaultIncluded(),
            Lazy::create(fn () => $product->created_at)->defaultIncluded(),
            Lazy::create(fn () => $product->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $product->deleted_at)->defaultIncluded(),
        );
    }
}
