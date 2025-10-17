<?php

namespace App\DTOs\Service;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\Category\CategoryResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\ServiceQuarter\ServiceQuarterResponseDTO;
use App\Models\Service;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ServiceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $fortnoxArticleId,
        public Lazy|null|string $type,
        public Lazy|null|string $membershipType,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|float $price,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|int $vatGroup,
        public Lazy|null|bool $hasRut,
        public Lazy|null|string $thumbnailImage,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        #[DataCollectionOf(CategoryResponseDTO::class)]
        public Lazy|null|DataCollection $categories,
        #[DataCollectionOf(AddonResponseDTO::class)]
        public Lazy|null|DataCollection $addons,
        #[DataCollectionOf(ProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        public Lazy|null|array $translations,
        #[DataCollectionOf(ServiceQuarterResponseDTO::class)]
        public Lazy|null|DataCollection $quarters,
    ) {
    }

    public static function fromModel(Service $service): self
    {
        return new self(
            Lazy::create(fn () => $service->id)->defaultIncluded(),
            Lazy::create(fn () => $service->fortnox_article_id)->defaultIncluded(),
            Lazy::create(fn () => $service->type)->defaultIncluded(),
            Lazy::create(fn () => $service->membership_type)->defaultIncluded(),
            Lazy::create(fn () => $service->name)->defaultIncluded(),
            Lazy::create(fn () => $service->description)->defaultIncluded(),
            Lazy::create(fn () => $service->price)->defaultIncluded(),
            Lazy::create(fn () => $service->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $service->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $service->has_rut)->defaultIncluded(),
            Lazy::create(fn () => $service->thumbnail_image)->defaultIncluded(),
            Lazy::create(fn () => $service->created_at)->defaultIncluded(),
            Lazy::create(fn () => $service->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $service->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => CategoryResponseDTO::collection($service->categories)),
            Lazy::create(fn () => AddonResponseDTO::collection($service->addons)),
            Lazy::create(fn () => ProductResponseDTO::collection($service->products)),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($service->tasks)),
            Lazy::create(fn () => static::getTranslations($service))->defaultIncluded(),
            Lazy::create(fn () => ServiceQuarterResponseDTO::collection($service->quarters)),
        );
    }
}
