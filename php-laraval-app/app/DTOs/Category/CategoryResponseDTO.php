<?php

namespace App\DTOs\Category;

use App\DTOs\BaseData;
use App\Models\Category;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CategoryResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|string $thumbnailImage,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|array $translations,
    ) {
    }

    public static function fromModel(Category $category): self
    {
        return new self(
            Lazy::create(fn () => $category->id)->defaultIncluded(),
            Lazy::create(fn () => $category->name)->defaultIncluded(),
            Lazy::create(fn () => $category->description)->defaultIncluded(),
            Lazy::create(fn () => $category->thumbnail_image)->defaultIncluded(),
            Lazy::create(fn () => $category->created_at)->defaultIncluded(),
            Lazy::create(fn () => $category->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $category->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => static::getTranslations($category))->defaultIncluded(),
        );
    }
}
