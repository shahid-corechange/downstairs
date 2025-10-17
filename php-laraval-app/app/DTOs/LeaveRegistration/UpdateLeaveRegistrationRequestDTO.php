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
class UpdateLeaveRegistrationRequestDTO extends BaseData
{
    public function __construct(
        public string|Optional $type,
        public Carbon|Optional $start_at,
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
            'type' => ['string', Rule::in(AbsenceTypeEnum::values())],
            'start_at' => 'date',
            'end_at' => "{$isEndAtRequired}|date|after:start_at",
        ];
    }
}
