<?php

namespace App\DTOs\Fortnox\Employee;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class EmployeeDTO extends BaseData
{
    public function __construct(
        public ?string $employee_id,
        public ?string $personal_identity_number,
        public ?string $first_name,
        public ?string $last_name,
        public ?string $full_name,
        public ?string $address1,
        public ?string $address2,
        public ?string $post_code,
        public ?string $city,
        public ?string $country,
        public ?string $phone1,
        public ?string $phone2,
        public ?string $email,
        public ?string $employment_date,
        public ?string $employment_form,
        public ?string $salary_form,
        public ?string $job_title,
        public ?string $personel_type,
        public ?string $schedule_id,
        public ?string $fora_type,
        public ?string $monthly_salary,
        public ?string $hourly_pay,
        public ?string $tax_allowance,
        public ?string $tax_table,
        public ?int $tax_column,
        public ?bool $auto_non_recuring_tax,
        public ?string $non_recuring_tax,
        public ?bool $inactive,
        public ?string $clearing_no,
        public ?string $bank_account_no,
        public ?string $employed_to,
        public ?string $average_weekly_hours,
        public ?string $average_hourly_wage,
        #[DataCollectionOf(EmployeeDatedWageDTO::class)]
        public ?DataCollection $dated_wages,
        #[DataCollectionOf(EmployeeDatedScheduleDTO::class)]
        public ?DataCollection $dated_schedules,
    ) {
    }
}
