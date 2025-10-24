<?php

namespace App\DTOs\Customer;

use App\DTOs\BaseData;
use App\Enums\Invoice\InvoiceDueDaysEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\User\UserNotificationMethodEnum;
use App\Enums\User\UserStatusEnum;
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
class UpdatePrimaryCustomerRequestDTO extends BaseData
{
    public function __construct(
        public string $membership_type,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|Optional $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $email,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $phone1,
        public int|Optional $due_days,
        public string|Optional $invoice_method,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $reference,
        #[WithTransformer(StringTransformer::class)]
        public array|Optional $meta,
        // address
        public int|Optional $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $address_2,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $area,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $postal_code,
        public float|Optional $latitude,
        public float|Optional $longitude,
        // user
        #[WithTransformer(StringTransformer::class)]
        public string $first_name,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $last_name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $user_email,
        #[WithTransformer(StringTransformer::class)]
        public string $cellphone,
        public string|Optional $status,
        // user info
        public string|null|Optional $language,
        public string|null|Optional $timezone,
        public string|Optional $notification_method,
    ) {
    }

    public static function rules(): array
    {
        $identityNumber = ['string'];

        if (request('membershipType') === MembershipTypeEnum::Private()) {
            $identityNumber[] = new SwedishSocialSecurityNumber();
        }

        $user = request()->route('user');
        if (! $user) {
            /** @var Customer $customer */
            $customer = request()->route('customer');
            $user = $customer->membership_type === MembershipTypeEnum::Company() ?
                $customer->companyUser : $customer->users->first();
        }

        $emailRules = $user ? '|unique:users,email,'.$user['id'] : '|unique:users,email';

        return [
            'membership_type' => ['required', Rule::in(MembershipTypeEnum::values())],
            'identity_number' => $identityNumber,
            'name' => 'string',
            'email' => 'email',
            'phone1' => 'string',
            'due_days' => ['numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => ['string', Rule::in(InvoiceMethodEnum::values())],
            'reference' => 'nullable|string',
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'numeric|exists:cities,id',
            'address' => 'string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'string',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            // user
            'first_name' => 'string|max:255',
            'last_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email'.$emailRules,
            'cellphone' => ['string', 'max:16', new UserUniqueCellphone($user)],
            'status' => [Rule::notIn([UserStatusEnum::Deleted()]), Rule::in(UserStatusEnum::values())],
            // user info
            'language' => 'nullable|string',
            'timezone' => 'nullable|string',
            'notification_method' => [Rule::in(UserNotificationMethodEnum::values())],
        ];
    }
}
