<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleRefundResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $amount,
        public Lazy|null|string $validUntil,
    ) {
    }
}
