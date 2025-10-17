<?php

namespace App\DTOs\Property;

use App\DTOs\Address\UpdateAddressRequestDTO;
use App\DTOs\BaseData;
use App\Enums\MembershipTypeEnum;
use App\Enums\Property\PropertyTypeEnum;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdatePropertyRequestDTO extends BaseData
{
    public function __construct(
        public int|Optional $address_id,
        public string|Optional $type,
        public string|Optional $membership_type,
        public float|Optional $square_meter,
        public array|Optional $meta,
        public KeyInformationRequestDTO|Optional $key_information,
        public UpdateAddressRequestDTO $address,
    ) {
    }

    public static function rules(): array
    {
        return [
            'address_id' => 'numeric|exists:addresses,id',
            'type' => [Rule::in(PropertyTypeEnum::values())],
            'membership_type' => [Rule::in(MembershipTypeEnum::values())],
            'square_meter' => 'numeric',
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
            'key_information' => 'array',
            'address' => 'array',
        ];
    }
}
