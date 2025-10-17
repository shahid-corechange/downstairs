<?php

namespace App\DTOs\Cart;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateCheckoutRequestDTO extends BaseData
{
    public function __construct(
        #[DataCollectionOf(CreateCartAddOnRequestDTO::class)]
        public DataCollection $addons,
        public bool $use_credit
    ) {
    }

    public static function rules(): array
    {
        return [
            'addons' => 'required|array|min:1',
            'use_credit' => 'required|boolean',
        ];
    }
}
