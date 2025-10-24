<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Enums\Invoice\InvoiceDueDaysEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\User\UserNotificationMethodEnum;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use App\Rules\UserUniqueCellphone;
use App\Transformers\SocialSecurityNumberTransformer;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateUserCashierCompanyCustomerRequestDTO extends BaseData
{
    public function __construct(
        public int $customer_id,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|Optional $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        public string|Optional $membership_type,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $email,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $phone1,
        public int|Optional $due_days,
        public string|Optional $invoice_method,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $reference,
        #[WithTransformer(StringTransformer::class)]
        public array|Optional $meta,
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
        // discount
        public int|null|Optional $discount_id,
        public float|null|Optional $discount_percentage,
        // user
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $first_name,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $cellphone,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $user_email,
        public string|Optional $notification_method,
    ) {
    }

    public static function rules(): array
    {
        $user = request()->route('user');
        $emailRules = $user ? '|unique:users,email,'.$user['id'] : '|unique:users,email';

        return [
            'customer_id' => 'required|numeric|exists:customers,id',
            'identity_number' => 'nullable|string',
            'name' => 'string',
            'membership_type' => 'string',
            'email' => 'nullable|email'.$emailRules,
            'phone1' => 'string',
            'due_days' => ['numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => ['string', Rule::in(InvoiceMethodEnum::values())],
            'reference' => 'nullable|string',
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'nullable|required_with:address,postal_code|numeric|exists:cities,id',
            'address' => 'nullable|required_with:city_id,postal_code|string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'nullable|required_with:address,city_id|string',
            'latitude' => 'nullable|required_with:address,postal_code,city_id|numeric',
            'longitude' => 'nullable|required_with:address,postal_code,city_id|numeric',
            // discount
            'discount_id' => 'nullable|numeric|exists:customer_discounts,id',
            'discount_percentage' => 'required_with:discount_id|numeric|min:0|max:100',
            // user
            'first_name' => 'string',
            'cellphone' => ['string', 'max:16', new UserUniqueCellphone($user)],
            'user_email' => 'nullable|email'.$emailRules,
            // user info
            'notification_method' => [Rule::in(UserNotificationMethodEnum::values())],
        ];
    }
}
