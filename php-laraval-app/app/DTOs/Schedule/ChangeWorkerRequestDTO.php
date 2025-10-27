<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\Rules\Worker;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class ChangeWorkerRequestDTO extends BaseData
{
    public function __construct(
        #[Rule(['required', 'numeric', 'exists:users,id', new Worker()])]
        public int $user_id,
        #[Rule('required|numeric|exists:schedules,id')]
        public int $schedule_id,
        #[Rule('required|numeric|exists:schedule_employees,id')]
        public int $schedule_employee_id
    ) {
    }
}
