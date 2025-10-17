<?php

namespace App\DTOs\ScheduleEmployee;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CancelScheduleEmployeeRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $description,
    ) {
    }

    public static function rules(): array
    {
        return [
            'description' => 'string',
        ];
    }
}
