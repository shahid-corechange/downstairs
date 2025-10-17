<?php

namespace App\DTOs\Role;

use App\DTOs\BaseData;
use App\DTOs\Permission\PermissionResponseDTO;
use App\Models\Role;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class RoleResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        #[DataCollectionOf(PermissionResponseDTO::class)]
        public Lazy|null|DataCollection $permissions,
    ) {
    }

    public static function fromModel(Role $role): self
    {
        return new self(
            Lazy::create(fn () => $role->id)->defaultIncluded(),
            Lazy::create(fn () => $role->name)->defaultIncluded(),
            Lazy::create(fn () => PermissionResponseDTO::transformCollection($role->permissions)),
        );
    }
}
