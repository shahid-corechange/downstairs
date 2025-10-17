<?php

namespace App\DTOs\Category;

use App\DTOs\BaseData;
use App\DTOs\Translation\CreateDefaultTranslationRequestDTO;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateCategoryRequestDTO extends BaseData
{
    public function __construct(
        public CreateDefaultTranslationRequestDTO $name,
        public CreateDefaultTranslationRequestDTO $description,
        public UploadedFile|Optional $thumbnail,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'thumbnail' => 'image|max:5120|mimes:svg',
        ];
    }
}
