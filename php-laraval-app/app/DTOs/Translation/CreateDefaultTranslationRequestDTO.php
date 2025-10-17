<?php

namespace App\DTOs\Translation;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Optional;

class CreateDefaultTranslationRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        #[Rule('required', 'string')]
        public string $sv_SE,
        #[WithTransformer(StringTransformer::class)]
        #[Rule('required', 'string')]
        public string $en_US,
        #[WithTransformer(StringTransformer::class)]
        #[Rule('string')]
        public string|Optional $nn_NO,
    ) {
    }
}
