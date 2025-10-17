<?php

namespace App\DTOs\Addon;

use App\DTOs\BaseData;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\Models\Addon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class AddonResponseDTO extends BaseData
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
        public Lazy|null|string $thumbnailImage,
        public Lazy|null|string $color,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        #[DataCollectionOf(ServiceResponseDTO::class)]
        public Lazy|null|DataCollection $services,
        #[DataCollectionOf(CategoryResponseDTO::class)]
        public Lazy|null|DataCollection $categories,
        #[DataCollectionOf(ProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        public Lazy|null|array $translations,
    ) {
    }

    public static function fromModel(Addon $addon): self
    {
        return new self(
            Lazy::create(fn () => $addon->id)->defaultIncluded(),
            Lazy::create(fn () => $addon->fortnox_article_id)->defaultIncluded(),
            Lazy::create(fn () => $addon->name)->defaultIncluded(),
            Lazy::create(fn () => $addon->description)->defaultIncluded(),
            Lazy::create(fn () => $addon->unit)->defaultIncluded(),
            Lazy::create(fn () => $addon->price)->defaultIncluded(),
            Lazy::create(fn () => $addon->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $addon->app_price)->defaultIncluded(),
            Lazy::create(fn () => $addon->credit_price)->defaultIncluded(),
            Lazy::create(fn () => $addon->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $addon->has_rut)->defaultIncluded(),
            Lazy::create(fn () => $addon->thumbnail_image)->defaultIncluded(),
            Lazy::create(fn () => $addon->color)->defaultIncluded(),
            Lazy::create(fn () => $addon->created_at)->defaultIncluded(),
            Lazy::create(fn () => $addon->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $addon->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => ServiceResponseDTO::collection($addon->services)),
            Lazy::create(fn () => CategoryResponseDTO::collection($addon->categories)),
            Lazy::create(fn () => ProductResponseDTO::collection($addon->products)),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($addon->tasks)),
            Lazy::create(fn () => static::getTranslations($addon))->defaultIncluded(),
        );
    }
}
