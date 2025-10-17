<?php

namespace App\DTOs\Category;

use App\DTOs\BaseData;
use App\DTOs\Translation\UpdateDefaultTranslationRequestDTO;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateCategoryRequestDTO extends BaseData
{
    public function __construct(
        public UpdateDefaultTranslationRequestDTO $name,
        public UpdateDefaultTranslationRequestDTO $description,
        public UploadedFile|Optional $thumbnail,
    ) {
    }

    public static function rules(): array
    {
        return [
            'thumbnail' => 'image|max:5120|mimes:svg',
        ];
    }
}
