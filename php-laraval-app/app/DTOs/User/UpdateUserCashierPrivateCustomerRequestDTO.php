<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Enums\Invoice\InvoiceDueDaysEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
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
class UpdateUserCashierPrivateCustomerRequestDTO extends BaseData
{
    public function __construct(
        public int $customer_id,
        #[WithTransformer(SocialSecurityNumberTransformer::class)]
        public string|Optional $identity_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
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
    ) {
    }

    public static function rules(): array
    {
        return [
            'customer_id' => 'required|numeric|exists:customers,id',
            'identity_number' => ['string', new SwedishSocialSecurityNumber()],
            'name' => 'string',
            'email' => 'nullable|email',
            'phone1' => 'string',
            'due_days' => ['numeric', Rule::in(InvoiceDueDaysEnum::values())],
            'invoice_method' => ['string', Rule::in(InvoiceMethodEnum::values())],
            'reference' => 'nullable|string',
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
            // address
            'city_id' => 'nullable|required_with:email|numeric|exists:cities,id',
            'address' => 'nullable|required_with:email|string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'nullable|required_with:email|string',
            'latitude' => 'nullable|required_with:email|numeric',
            'longitude' => 'nullable|required_with:email|numeric',
            // discount
            'discount_id' => 'nullable|numeric|exists:customer_discounts,id',
            'discount_percentage' => 'nullable|required_with:discount_id|numeric|min:0|max:100',
        ];
    }
}
