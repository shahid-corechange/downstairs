<?php

namespace App\DTOs\Employee;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateEmployeeRoleRequestDTO extends BaseData
{
    public function __construct(
        public array $roles,
    ) {
    }

    public static function rules(): array
    {
        return [
            'roles' => 'required|array|min:1|not_in:Customer,Company',
            'roles.*' => 'required|string|exists:roles,name',
        ];
    }
}
