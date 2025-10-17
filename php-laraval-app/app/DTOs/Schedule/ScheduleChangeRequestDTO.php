<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\Rules\DateNotInBlockDays;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleChangeRequestDTO extends BaseData
{
    public function __construct(
        public float|Optional $squarefeet_changed,
        public Carbon|Optional $start_at_changed,
    ) {
    }

    public static function rules(): array
    {
        return [
            'squarefeet_changed' => 'numeric|gt:0',
            'start_at_changed' => ['date', 'after:today', new DateNotInBlockDays()],
        ];
    }
}
