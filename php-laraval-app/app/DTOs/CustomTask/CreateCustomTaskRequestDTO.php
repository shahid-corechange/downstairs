<?php

namespace App\DTOs\CustomTask;

use App\DTOs\BaseData;
use App\DTOs\Translation\CreateDefaultTranslationRequestDTO;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateCustomTaskRequestDTO extends BaseData
{
    public function __construct(
        public CreateDefaultTranslationRequestDTO $name,
        public CreateDefaultTranslationRequestDTO $description
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
        ];
    }
}
