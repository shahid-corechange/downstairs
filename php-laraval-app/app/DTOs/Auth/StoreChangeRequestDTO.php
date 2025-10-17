<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class StoreChangeRequestDTO extends BaseData
{
    public function __construct(
        public int|null|Optional $store_id,
    ) {
    }

    public static function rules(): array
    {
        return [
            'store_id' => 'nullable|integer|exists:stores,id',
        ];
    }
}
