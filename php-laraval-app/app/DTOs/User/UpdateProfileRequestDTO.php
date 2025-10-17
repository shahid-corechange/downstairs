<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Enums\User\User2FAEnum;
use App\Transformers\StringTransformer;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateProfileRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $first_name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $last_name,
        public string|Optional $timezone,
        public string|Optional $language,
        public string|Optional $currency,
        public string|Optional $two_factor_auth,
        public UploadedFile|Optional $avatar,
    ) {
    }

    public static function rules(): array
    {

        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'timezone' => 'string|max:255',
            'language' => 'string|max:255',
            'currency' => 'string|max:255',
            'two_factor_auth' => ['string', Rule::in(User2FAEnum::values())],
            'avatar' => 'image|max:5120',
        ];
    }
}
