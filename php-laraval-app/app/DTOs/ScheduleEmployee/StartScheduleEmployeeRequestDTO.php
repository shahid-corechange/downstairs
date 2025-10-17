<?php

namespace App\DTOs\ScheduleEmployee;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class StartScheduleEmployeeRequestDTO extends BaseData
{
    public function __construct(
        public float $start_latitude,
        public float $start_longitude,
        public string $start_ip,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $description,
    ) {
    }

    public static function rules(): array
    {
        return [
            'start_latitude' => 'required|numeric',
            'start_longitude' => 'required|numeric',
            'start_ip' => 'required|string',
            'description' => 'string',
        ];
    }
}
