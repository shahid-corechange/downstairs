<?php

namespace App\DTOs\Team;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Team;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class TeamResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|string $avatar,
        public Lazy|null|string $color,
        public Lazy|null|string $description,
        public Lazy|null|bool $isActive,
        public Lazy|null|int $totalWorkers,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        #[DataCollectionOf(UserResponseDTO::class)]
        public Lazy|null|DataCollection $users,
    ) {
    }

    public static function fromModel(Team $team): self
    {
        return new self(
            Lazy::create(fn () => $team->id)->defaultIncluded(),
            Lazy::create(fn () => $team->name)->defaultIncluded(),
            Lazy::create(fn () => $team->avatar)->defaultIncluded(),
            Lazy::create(fn () => $team->color)->defaultIncluded(),
            Lazy::create(fn () => $team->description)->defaultIncluded(),
            Lazy::create(fn () => $team->is_active)->defaultIncluded(),
            Lazy::create(fn () => $team->total_workers)->defaultIncluded(),
            Lazy::create(fn () => $team->created_at)->defaultIncluded(),
            Lazy::create(fn () => $team->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $team->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::collection($team->users)),
        );
    }
}
