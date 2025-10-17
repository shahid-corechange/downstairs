<?php

namespace App\DTOs\TimeAdjustment;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateTimeAdjustmentRequestDTO extends BaseData
{
    public function __construct(
        public int $quarters,
        public string $reason,
    ) {
    }

    public static function rules(): array
    {
        return [
            'quarters' => 'required|numeric',
            'reason' => 'required|string',
        ];
    }
}
