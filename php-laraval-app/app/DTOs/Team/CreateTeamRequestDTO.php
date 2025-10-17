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
class CreateTeamRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $name,
        public string $color,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $description,
        public array $user_ids,
        public UploadedFile|Optional $thumbnail,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|numeric|exists:users,id',
            'thumbnail' => 'image|max:5120',
        ];
    }
}
