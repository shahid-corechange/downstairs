<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\DTOs\Property\KeyInformationRequestDTO;
use App\Enums\Invoice\InvoiceDueDaysEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\User\User2FAEnum;
use App\Enums\User\UserNotificationMethodEnum;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use App\Rules\SwedishSocialSecurityNumber;
use App\Rules\UserUniqueCellphone;
use App\Transformers\SocialSecurityNumberTransformer;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UserCompanyCustomerWizardRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $company_name,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string $org_number,
        #[WithTransformer(StringTransformer::class)]
        public string $company_email,
        #[WithTransformer(StringTransformer::class)]
        public string $company_phone,
        public int $due_days,
        public string $invoice_method,
        // contact person
        public int|null|Optional $contact_id,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $first_name,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $last_name,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $email,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $cellphone,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|null|Optional $identity_number,
        public array|Optional $customer_meta,
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
        public float $latitude,
        public float $longitude,
        // property
        public int $property_type_id,
        public float $square_meter,
        public KeyInformationRequestDTO|null|Optional $key_information,
        public array|Optional $property_meta,
        // info
        public string|Optional $avatar,
        public string $language,
        public string $timezone,
        public string $currency,
        public string $two_factor_auth,
        // address invoice
        public int|null|Optional $invoice_city_id,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $invoice_address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $invoice_area,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $invoice_postal_code,
        public float|null|Optional $invoice_latitude,
        public float|null|Optional $invoice_longitude,
        // notification
        public string|null|Optional $notification_method,
    ) {
    }

    public static function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'org_number' => 'required|string',
            'company_email' => 'required|string|max:255|unique:users,email',
            'company_phone' => ['required', 'missing_with:contact_id', 'string', 'max:16', new UserUniqueCellphone()],
            'due_days' => ['required', 'numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => ['required', 'string', Rule::in(InvoiceMethodEnum::values())],
            // contact person
            'contact_id' => 'nullable|missing_with:first_name|numeric|exists:users,id',
            'first_name' => 'nullable|missing_with:contact_id|string|max:255',
            'last_name' => 'nullable|required_with:first_name|missing_with:contact_id|string|max:255',
            'email' => 'nullable|missing_with:contact_id|string|email|max:255|unique:users,email',
            'cellphone' => [
                'nullable',
                'missing_with:contact_id',
                'string',
                'max:16',
                'different:company_phone',
                new UserUniqueCellphone(),
            ],
            'identity_number' => ['nullable', 'missing_with:contact_id', 'string', new SwedishSocialSecurityNumber()],
            'customer_meta' => [new MetaRule()],
            'customer_meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'required|numeric|exists:cities,id',
            'address' => 'required|string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            // property
            'property_type_id' => 'required|numeric|exists:property_types,id',
            'square_meter' => 'numeric|min:1',
            'key_information' => 'nullable|array',
            'property_meta' => [new MetaRule()],
            'property_meta.*' => [new MetaProperty()],
            // info
            'avatar' => 'string',
            'language' => 'required|string',
            'timezone' => 'required|string',
            'currency' => 'required|string',
            'two_factor_auth' => ['required', 'string', Rule::in(User2FAEnum::values())],
            // address invoice
            'invoice_city_id' => 'nullable|numeric|exists:cities,id',
            'invoice_address' => 'nullable|string',
            'invoice_area' => 'nullable|string',
            'invoice_postal_code' => 'nullable|string',
            'invoice_latitude' => 'nullable|numeric',
            'invoice_longitude' => 'nullable|numeric',
            // notification
            'notification_method' => ['required', 'string', Rule::in(UserNotificationMethodEnum::values())],
        ];
    }
}
