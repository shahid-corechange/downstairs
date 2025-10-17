<?php

namespace App\DTOs\Role;

use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateRoleRequestDTO extends Data
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $name,
        public array $permissions,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'required|string|exists:permissions,name',
        ];
    }
}
