<?php

namespace App\DTOs\Role;

use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateRoleRequestDTO extends Data
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        public array|Optional $permissions,
    ) {
    }

    public static function rules(): array
    {
        $role = request()->route('role');

        return [
            'name' => 'string|unique:roles,name,'.$role['id'],
            'permissions' => 'array|min:1',
            'permissions.*' => 'required|string|exists:permissions,name',
        ];
    }
}
