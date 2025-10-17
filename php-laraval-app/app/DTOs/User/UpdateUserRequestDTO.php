<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Enums\User\UserNotificationMethodEnum;
use App\Enums\User\UserStatusEnum;
use App\Rules\UserUniqueCellphone;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateUserRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $first_name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $last_name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $email,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $cellphone,
        public string|Optional $timezone,
        public string|Optional $language,
        public string|Optional $notification_method,
        public string|Optional $status,
    ) {
    }

    public static function rules(): array
    {
        $user = request()->route('user');
        $emailRules = $user ? '|unique:users,email,'.$user['id'] : '|unique:users,email';

        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'identity_number' => 'string|max:255',
            'email' => 'string|email|max:255'.$emailRules,
            'cellphone' => ['string', 'max:16', new UserUniqueCellphone($user)],
            'notification_method' => [Rule::in(UserNotificationMethodEnum::values())],
            'status' => [Rule::notIn([UserStatusEnum::Deleted()]), Rule::in(UserStatusEnum::values())],
        ];
    }
}
