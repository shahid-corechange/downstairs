<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\DTOs\TimeAdjustment\UpdateTimeAdjustmentRequestDTO;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateWorkerAttendanceRequestDTO extends BaseData
{
    public function __construct(
        public string $start_at,
        public string $end_at,
        public UpdateTimeAdjustmentRequestDTO|Optional|null $time_adjustment,
    ) {
    }

    public static function rules(): array
    {
        return [
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'time_adjustment' => 'nullable',
        ];
    }
}
