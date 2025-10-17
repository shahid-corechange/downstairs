<?php

namespace App\DTOs\Team;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateTeamRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        public string|Optional $color,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $description,
        public array|Optional $user_ids,
        public UploadedFile|Optional $thumbnail,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'color' => 'string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'array|min:1',
            'user_ids.*' => 'numeric|exists:users,id',
            'thumbnail' => 'image|max:5120',
        ];
    }
}
