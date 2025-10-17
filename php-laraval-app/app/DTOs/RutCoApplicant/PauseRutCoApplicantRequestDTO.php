<?php

namespace App\DTOs\RutCoApplicant;

use App\DTOs\BaseData;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class PauseRutCoApplicantRequestDTO extends BaseData
{
    public function __construct(
        public Carbon $pause_start_date,
        public Carbon|Optional|null $pause_end_date,
    ) {
    }

    public static function rules(): array
    {
        $firstDayOfMonth = date('Y-m-01');

        return [
            'pause_start_date' => 'required|date:Y-m-d|after_or_equal:'.$firstDayOfMonth,
            'pause_end_date' => 'nullable|date:Y-m-d|after:pause_start_date',
        ];
    }
}
