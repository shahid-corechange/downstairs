<?php

namespace App\DTOs\BlockDay;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateBlockDayRequestDTO extends BaseData
{
    public function __construct(
        public string $block_date,
        public string|Optional $start_block_time = '00:00:00',
        public string|Optional $end_block_time = '23:59:59',
    ) {
    }

    public static function rules(): array
    {
        return [
            'block_date' => 'required|date_format:Y-m-d',
            'start_block_time' => 'date_format:H:i:s',
            'end_block_time' => 'date_format:H:i:s|after:start_block_time',
        ];
    }
}
