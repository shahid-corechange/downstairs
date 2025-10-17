<?php

namespace App\DTOs\Feedback;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateFeedbackRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $option,
        #[WithTransformer(StringTransformer::class)]
        public string $description,
    ) {
    }

    public static function rules(): array
    {
        return [
            'option' => 'required|string|max:255',
            'description' => 'string',
        ];
    }
}
