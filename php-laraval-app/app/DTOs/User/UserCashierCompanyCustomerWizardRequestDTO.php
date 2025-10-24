<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
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
class UserCashierCompanyCustomerWizardRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $company_name,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $company_email,
        #[WithTransformer(StringTransformer::class)]
        public string $company_phone,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string $org_number,
        public int $due_days,
        public string|null|Optional $invoice_method,
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
        public int|null|Optional $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $address_2,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $area,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $postal_code,
        public float|null|Optional $latitude,
        public float|null|Optional $longitude,
        // info
        public string|null|Optional $avatar,
        public string|null|Optional $language,
        public string|null|Optional $timezone,
        public string|null|Optional $currency,
        public string|null|Optional $two_factor_auth,
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
        // discount
        public float|null|Optional $discount_percentage,
        // notification
        public string|null|Optional $notification_method,
    ) {
    }

    public static function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'org_number' => 'required|string',
            'company_email' => 'nullable|string|max:255|unique:users,email',
            'company_phone' => ['required', 'string', 'max:16', new UserUniqueCellphone()],
            'due_days' => ['required', 'numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => [
                'nullable',
                'string',
                Rule::in(InvoiceMethodEnum::values()),
            ],
            // contact person
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'cellphone' => ['nullable', 'string', 'max:16', new UserUniqueCellphone()],
            'identity_number' => ['nullable', 'string', new SwedishSocialSecurityNumber()],
            'customer_meta' => [new MetaRule()],
            'customer_meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'nullable|numeric|exists:cities,id',
            'address' => 'nullable|string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            // info
            'avatar' => 'string',
            'language' => 'string',
            'timezone' => 'string',
            'currency' => 'string',
            'two_factor_auth' => ['string', Rule::in(User2FAEnum::values())],
            // address invoice
            'invoice_city_id' => 'nullable|numeric|exists:cities,id',
            'invoice_address' => 'nullable|string',
            'invoice_area' => 'nullable|string',
            'invoice_postal_code' => 'nullable|string',
            'invoice_latitude' => 'nullable|numeric',
            'invoice_longitude' => 'nullable|numeric',
            // discount
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            // notification
            'notification_method' => ['required', 'string', Rule::in(UserNotificationMethodEnum::values())],
        ];
    }
}
