<?php

namespace App\DTOs\Setting;

use App\DTOs\BaseData;
use App\Models\GlobalSetting;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class GlobalSettingResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $key,
        public Lazy|null|string $value,
        public Lazy|null|string $type,
        public Lazy|null|string $description,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
    ) {
    }

    public static function fromModel(GlobalSetting $globalSetting): self
    {
        return new self(
            Lazy::create(fn () => $globalSetting->id)->defaultIncluded(),
            Lazy::create(fn () => $globalSetting->key)->defaultIncluded(),
            Lazy::create(fn () => $globalSetting->value)->defaultIncluded(),
            Lazy::create(fn () => $globalSetting->type)->defaultIncluded(),
            Lazy::create(fn () => $globalSetting->description)->defaultIncluded(),
            Lazy::create(fn () => $globalSetting->created_at)->defaultIncluded(),
            Lazy::create(fn () => $globalSetting->updated_at)->defaultIncluded(),
        );
    }
}
