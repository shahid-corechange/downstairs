<?php

namespace App\DTOs\RutCoApplicant;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\RutCoApplicant;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class RutCoApplicantResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|string $identityNumber,
        public Lazy|null|string $name,
        public Lazy|null|string $phone,
        public Lazy|null|string $dialCode,
        public Lazy|null|string $formattedPhone,
        public Lazy|null|string $pauseStartDate,
        public Lazy|null|string $pauseEndDate,
        public Lazy|null|bool $isEnabled,
        public Lazy|null|bool $isPaused,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|UserResponseDTO $user,
    ) {
    }

    public static function fromModel(RutCoApplicant $rutCoApplicant): self
    {
        return new self(
            Lazy::create(fn () => $rutCoApplicant->id)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->user_id)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->identity_number)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->name)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->phone)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->dial_code)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->formatted_phone)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->pause_start_date?->format('Y-m-d'))->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->pause_end_date?->format('Y-m-d'))->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->is_enabled)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->is_paused)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->created_at)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $rutCoApplicant->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($rutCoApplicant->user)),
        );
    }
}
