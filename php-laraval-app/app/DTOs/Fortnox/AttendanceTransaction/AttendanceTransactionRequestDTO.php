<?php

namespace App\DTOs\Fortnox\AttendanceTransaction;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class AttendanceTransactionRequestDTO extends BaseData
{
    public function __construct(
        public string $employee_id,
        public string $cause_code,
        public string $date,
        public float $hours,
        public string|Optional $cost_center,
        public string|Optional $project,
    ) {
    }
}
