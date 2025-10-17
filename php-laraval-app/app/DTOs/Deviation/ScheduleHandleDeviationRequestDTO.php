<?php

namespace App\DTOs\Deviation;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleHandleDeviationRequestDTO extends BaseData
{
    public function __construct(
        public int $actual_quarters,
        #[DataCollectionOf(ScheduleDeviationItemRequestDTO::class)]
        public ?DataCollection $items,
    ) {
    }

    public static function rules(): array
    {
        return [
            'actual_quarters' => 'required|int',
        ];
    }
}
