<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleUpdateRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional|null $key_information,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional|null $note,
    ) {
    }

    public static function rules(): array
    {
        return [
            'key_information' => 'nullable|string',
            'note' => 'nullable|string',
        ];
    }
}
