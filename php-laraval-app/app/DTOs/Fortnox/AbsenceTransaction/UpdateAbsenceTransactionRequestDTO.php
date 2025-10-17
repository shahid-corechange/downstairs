<?php

namespace App\DTOs\Fortnox\AbsenceTransaction;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class UpdateAbsenceTransactionRequestDTO extends BaseData
{
    public function __construct(
        public string|Optional $cause_code,
        public string|Optional $cost_center,
        public string|Optional $date,
        public string|Optional $employee_id,
        public float|Optional $extent,
        public bool|Optional $holiday_entitling,
        public float|Optional $hours,
        public string|Optional $project,
    ) {
    }
}
