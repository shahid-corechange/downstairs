<?php

namespace App\DTOs\TimeAdjustment;

use App\DTOs\BaseData;
use App\Rules\AvailableScheduleAdjustment;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateTimeAdjustmentRequestDTO extends BaseData
{
    public function __construct(
        public int $schedule_employee_id,
        public int $quarters,
        public string $reason,
    ) {
    }

    public static function rules(): array
    {
        return [
            'schedule_employee_id' => [
                'required',
                'numeric',
                'exists:schedule_employees,id',
                new AvailableScheduleAdjustment(),
            ],
            'quarters' => 'required|numeric',
            'reason' => 'required|string',
        ];
    }
}
