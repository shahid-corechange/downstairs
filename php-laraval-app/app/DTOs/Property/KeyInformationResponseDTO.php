<?php

namespace App\DTOs\Property;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class KeyInformationResponseDTO extends BaseData
{
    public function __construct(
        public ?string $keyPlace,
        public ?string $frontDoorCode,
        public ?string $alarmCodeOff,
        public ?string $alarmCodeOn,
        public ?string $information,
    ) {
    }
}
