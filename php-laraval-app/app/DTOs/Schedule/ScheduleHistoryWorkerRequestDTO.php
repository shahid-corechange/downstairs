<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleHistoryWorkerRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:users,id')]
        public int $user_id,
        #[Rule('required|date')]
        public string $start_at,
        #[Rule('required|date|after:start_at')]
        public string $end_at,
    ) {
    }
}
