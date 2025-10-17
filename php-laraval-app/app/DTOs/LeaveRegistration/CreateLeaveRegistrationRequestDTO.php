<?php

namespace App\DTOs\LeaveRegistration;

use App\DTOs\BaseData;
use App\Enums\LeaveRegistration\AbsenceTypeEnum;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateLeaveRegistrationRequestDTO extends BaseData
{
    public function __construct(
        public int $employee_id,
        public string $type,
        public Carbon $start_at,
        public Carbon|null|Optional $end_at,
    ) {
    }

    public static function rules(): array
    {
        $isEndAtRequired = in_array(
            request()->input('type'),
            [AbsenceTypeEnum::Vacation(), AbsenceTypeEnum::UnpaidVacation()]
        ) ? 'required' : 'nullable';

        return [
            'employee_id' => 'required|numeric|exists:employees,id',
            'type' => ['required', 'string', Rule::in(AbsenceTypeEnum::values())],
            'start_at' => 'required|date',
            'end_at' => "{$isEndAtRequired}|date|after_or_equal:start_at",
        ];
    }
}
