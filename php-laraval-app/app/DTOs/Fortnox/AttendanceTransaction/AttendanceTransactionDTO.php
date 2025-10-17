<?php

namespace App\DTOs\Fortnox\AttendanceTransaction;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class AttendanceTransactionDTO extends BaseData
{
    public function __construct(
        public ?string $id,
        public ?string $employee_id,
        public ?string $cause_code,
        public ?string $date,
        public ?float $hours,
        public ?string $cost_center,
        public ?string $project,
    ) {
    }
}
