<?php

namespace App\DTOs\Permission;

use App\DTOs\BaseData;
use App\Models\Permission;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class PermissionResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
    ) {
    }

    public static function fromModel(Permission $permission): self
    {
        return new self(
            Lazy::create(fn () => $permission->id)->defaultIncluded(),
            Lazy::create(fn () => $permission->name)->defaultIncluded(),
        );
    }
}
