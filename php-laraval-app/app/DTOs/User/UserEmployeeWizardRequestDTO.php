<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Enums\User\User2FAEnum;
use App\Rules\EmployeeName;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use App\Rules\UserUniqueCellphone;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UserEmployeeWizardRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $first_name,
        #[WithTransformer(StringTransformer::class)]
        public string $last_name,
        #[WithTransformer(StringTransformer::class)]
        public string $email,
        #[WithTransformer(StringTransformer::class)]
        public string $cellphone,
        #[WithTransformer(StringTransformer::class)]
        public string $identity_number,
        public array $roles,
        public array|Optional $meta,
        // address
        public int $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string $address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $address_2,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $area,
        #[WithTransformer(StringTransformer::class)]
        public string $postal_code,
        public float|null|Optional $latitude,
        public float|null|Optional $longitude,
        // info
        public string|Optional $avatar,
        public string $language,
        public string $timezone,
        public string $currency,
        public string $two_factor_auth,
    ) {
    }

    public static function rules(): array
    {
        $notAllowedRoles = [
            'Superadmin',
            'Customer',
            'Guest',
        ];

        return [
            'first_name' => ['required', 'string', new EmployeeName()],
            'last_name' => ['required', 'string', new EmployeeName()],
            'email' => 'required|string|email|max:255|unique:users',
            'cellphone' => ['required', 'string', 'max:16', new UserUniqueCellphone()],
            'identity_number' => 'required|string',
            'roles' => 'array',
            'roles.*' => ['string', 'exists:roles,name', Rule::notIn($notAllowedRoles)],
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'required|numeric|exists:cities,id',
            'address' => 'required|string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            // info
            'avatar' => 'string',
            'language' => 'required|string',
            'timezone' => 'required|string',
            'currency' => 'required|string',
            'two_factor_auth' => ['required', 'string', Rule::in(User2FAEnum::values())],
        ];
    }
}
