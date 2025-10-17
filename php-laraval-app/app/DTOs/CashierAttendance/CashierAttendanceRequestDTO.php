<?php

namespace App\DTOs\CashierAttendance;

use App\DTOs\BaseData;
use App\Rules\CashierInStore;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CashierAttendanceRequestDTO extends BaseData
{
    public function __construct(
        public int $user_id,
    ) {
    }

    public static function rules()
    {
        return [
            'user_id' => ['required', 'numeric', new CashierInStore()],
        ];
    }
}
