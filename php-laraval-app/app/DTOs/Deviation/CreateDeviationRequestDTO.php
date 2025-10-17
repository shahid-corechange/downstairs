<?php

namespace App\DTOs\Deviation;

use App\DTOs\BaseData;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateDeviationRequestDTO extends BaseData
{
    public function __construct(
        public int|Optional $user_id,
        public int $schedule_id,
        public string $type,
        #[WithTransformer(StringTransformer::class)]
        public string $reason,
    ) {
    }

    public static function rules(): array
    {
        return [
            'user_id' => 'numeric|exists:users,id',
            'schedule_id' => 'required|numeric|exists:schedules,id',
            'type' => ['required', Rule::in(DeviationTypeEnum::values())],
            'reason' => 'required|string',
        ];
    }
}
