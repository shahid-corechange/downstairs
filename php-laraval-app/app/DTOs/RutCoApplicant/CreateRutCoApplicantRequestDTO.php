<?php

namespace App\DTOs\RutCoApplicant;

use App\DTOs\BaseData;
use App\Rules\SwedishSocialSecurityNumber;
use App\Transformers\SocialSecurityNumberTransformer;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateRutCoApplicantRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string $name,
        #[WithTransformer(StringTransformer::class)]
        public string $phone,
    ) {
    }

    public static function rules(): array
    {
        return [
            'identity_number' => ['required', 'string', new SwedishSocialSecurityNumber()],
            'name' => 'required|string',
            'phone' => 'required|string',
        ];
    }
}
