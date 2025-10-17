<?php

namespace App\DTOs\Fortnox\AbsenceTransaction;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class AbsenceTransactionDTO extends BaseData
{
    public function __construct(
        public ?string $cause_code,
        public ?string $cost_center,
        public ?string $date,
        public ?string $employee_id,
        public ?float $extent,
        public ?bool $holiday_entitling,
        public ?float $hours,
        public ?string $project,
        public ?string $id,
    ) {
    }
}
