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
class UserCustomerWizardRequestDTO extends BaseData
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
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string $identity_number,
        public int $due_days,
        public string $invoice_method,
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'cellphone' => ['required', 'string', 'max:16', new UserUniqueCellphone()],
            'identity_number' => ['required', 'string', new SwedishSocialSecurityNumber()],
            'due_days' => ['required', 'numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => ['required', 'string', Rule::in(InvoiceMethodEnum::values())],
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
