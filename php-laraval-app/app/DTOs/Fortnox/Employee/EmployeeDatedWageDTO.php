<?php

namespace App\DTOs\Fortnox\Employee;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class EmployeeDatedWageDTO extends BaseData
{
    public function __construct(
        public ?string $employee_id,
        public ?string $first_day,
        public ?string $monthly_salary,
        public ?string $hourly_pay,
    ) {
    }
}
