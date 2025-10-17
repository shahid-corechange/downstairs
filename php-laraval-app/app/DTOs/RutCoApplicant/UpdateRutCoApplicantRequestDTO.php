<?php

namespace App\DTOs\RutCoApplicant;

use App\DTOs\BaseData;
use App\Rules\SwedishSocialSecurityNumber;
use App\Transformers\SocialSecurityNumberTransformer;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateRutCoApplicantRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|Optional|null $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional|null $name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional|null $phone,
    ) {
    }

    public static function rules(): array
    {
        return [
            'identity_number' => ['string', new SwedishSocialSecurityNumber()],
            'name' => 'string',
            'phone' => 'string',
        ];
    }
}
