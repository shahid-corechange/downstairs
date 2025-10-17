<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleBackOfficeCancelRequestDTO extends BaseData
{
    public function __construct(
        public bool $refund,
    ) {
    }

    public static function rules(): array
    {
        return [
            'refund' => 'required|boolean',
        ];
    }
}
