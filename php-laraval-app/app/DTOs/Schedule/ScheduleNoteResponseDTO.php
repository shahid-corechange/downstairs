<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleNoteResponseDTO extends BaseData
{
    public function __construct(
        public ?string $propertyNote,
        public ?string $subscriptionNote,
        public ?string $note,
    ) {
    }
}
