<?php

namespace App\DTOs\CustomTask;

use App\DTOs\BaseData;
use App\Models\CustomTask;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CustomTaskResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|array $translations,
    ) {
    }

    public static function fromModel(CustomTask $task): self
    {
        return new self(
            Lazy::create(fn () => $task->id)->defaultIncluded(),
            Lazy::create(fn () => $task->name)->defaultIncluded(),
            Lazy::create(fn () => $task->description)->defaultIncluded(),
            Lazy::create(fn () => $task->created_at),
            Lazy::create(fn () => $task->updated_at),
            Lazy::create(fn () => static::getTranslations($task))->defaultIncluded(),
        );
    }
}
