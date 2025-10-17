<?php

namespace App\DTOs\UnassignSubscription;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\Models\Product;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UnassignSubscriptionProductResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $fortnoxArticleId,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|string $unit,
        public Lazy|null|float $price,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|float $appPrice,
        public Lazy|null|int $creditPrice,
        public Lazy|null|int $vatGroup,
        public Lazy|null|bool $hasRut,
        public Lazy|null|string $status,
        public Lazy|null|string $thumbnailImage,
        public Lazy|null|string $color,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        #[DataCollectionOf(CategoryResponseDTO::class)]
        public Lazy|null|DataCollection $categories,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        #[DataCollectionOf(ServiceResponseDTO::class)]
        public Lazy|null|DataCollection $services,
        #[DataCollectionOf(AddonResponseDTO::class)]
        public Lazy|null|DataCollection $addons,
        #[DataCollectionOf(StoreResponseDTO::class)]
        public Lazy|null|DataCollection $stores,
        public Lazy|null|array $meta,
        public Lazy|null|array $translations,
        public Lazy|null|int $quantity,
    ) {
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            Lazy::create(fn () => $product->id)->defaultIncluded(),
            Lazy::create(fn () => $product->fortnox_article_id)->defaultIncluded(),
            Lazy::create(fn () => $product->name)->defaultIncluded(),
            Lazy::create(fn () => $product->description)->defaultIncluded(),
            Lazy::create(fn () => $product->unit)->defaultIncluded(),
            Lazy::create(fn () => $product->price)->defaultIncluded(),
            Lazy::create(fn () => $product->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $product->app_price)->defaultIncluded(),
            Lazy::create(fn () => $product->credit_price)->defaultIncluded(),
            Lazy::create(fn () => $product->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $product->has_rut)->defaultIncluded(),
            Lazy::create(fn () => $product->status)->defaultIncluded(),
            Lazy::create(fn () => $product->thumbnail_image)->defaultIncluded(),
            Lazy::create(fn () => $product->color)->defaultIncluded(),
            Lazy::create(fn () => $product->created_at)->defaultIncluded(),
            Lazy::create(fn () => $product->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $product->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => CategoryResponseDTO::collection($product->categories)),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($product->tasks)),
            Lazy::create(fn () => ServiceResponseDTO::collection($product->services)),
            Lazy::create(fn () => AddonResponseDTO::collection($product->addons)),
            Lazy::create(fn () => StoreResponseDTO::collection($product->stores)),
            Lazy::create(fn () => static::getModelMeta($product))->defaultIncluded(),
            Lazy::create(fn () => static::getTranslations($product))->defaultIncluded(),
            Lazy::create(fn () => $product->quantity)->defaultIncluded(),
        );
    }
}
