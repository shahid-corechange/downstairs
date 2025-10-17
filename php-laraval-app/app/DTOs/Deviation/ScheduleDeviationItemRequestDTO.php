<?php

namespace App\DTOs\Deviation;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleDeviationItemRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:schedule_items,id')]
        public int $id,
        #[Rule('required|boolean')]
        public bool $is_charge,
    ) {
    }
}
