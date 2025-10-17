<?php

namespace App\DTOs\Customer;

use App\DTOs\BaseData;
use App\Enums\User\UserNotificationMethodEnum;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use App\Transformers\SocialSecurityNumberTransformer;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateCompanyCustomerRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|Optional $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $email,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $phone1,
        public string|Optional $notification_method,
        public array|Optional $meta,
    ) {
    }

    public static function rules(): array
    {
        return [
            'identity_number' => 'string',
            'name' => 'string',
            'email' => 'email',
            'phone1' => 'string',
            'notification_method' => [Rule::in(UserNotificationMethodEnum::values())],
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
        ];
    }
}
