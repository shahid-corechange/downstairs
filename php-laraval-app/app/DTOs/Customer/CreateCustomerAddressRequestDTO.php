<?php

namespace App\DTOs\Customer;

use App\DTOs\BaseData;
use App\Enums\Invoice\InvoiceDueDaysEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\MembershipTypeEnum;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use App\Rules\SwedishSocialSecurityNumber;
use App\Transformers\SocialSecurityNumberTransformer;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateCustomerAddressRequestDTO extends BaseData
{
    public function __construct(
        public string $membership_type,
        #[WithTransformer(StringTransformer::class)]
        public string $email,
        public int $due_days,
        public string $invoice_method,
        public int|Optional $customer_ref_id,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|Optional $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $phone1,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $reference,
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
    ) {
    }

    public static function rules(): array
    {
        $identityNumber = ['required_without:customer_ref_id', 'string'];

        if (request('membershipType') === MembershipTypeEnum::Private()) {
            $identityNumber[] = new SwedishSocialSecurityNumber();
        }

        return [
            'membership_type' => ['required', Rule::in(MembershipTypeEnum::values())],
            'email' => 'required|email',
            'due_days' => ['required', 'numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => ['required', 'string', Rule::in(InvoiceMethodEnum::values())],
            'customer_ref_id' => 'numeric|exists:customers,id',
            'identity_number' => $identityNumber,
            'name' => 'required_without:customer_ref_id|string',
            'phone1' => 'required_without:customer_ref_id|string',
            'reference' => 'nullable|string',
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'required_without:customer_ref_id|numeric|exists:cities,id',
            'address' => 'required_without:customer_ref_id|string',
            'address_2' => 'missing_with:customer_ref_id|nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'required_without:customer_ref_id|string',
            'latitude' => 'required_without:customer_ref_id|numeric',
            'longitude' => 'required_without:customer_ref_id|numeric',
        ];
    }
}
