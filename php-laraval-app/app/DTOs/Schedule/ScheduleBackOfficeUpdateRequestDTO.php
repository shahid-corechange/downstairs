<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\Rules\ExistScheduleAddon;
use App\Rules\ExistScheduleProduct;
use App\Rules\ValidStart;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleBackOfficeUpdateRequestDTO extends BaseData
{
    public function __construct(
        public int|Optional|null $team_id,
        public string|Optional|null $start_at,
        public string|Optional|null $end_at,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $note,
        public array|Optional|null $remove_add_ons,
        #[DataCollectionOf(NewAddOnsRequestDTO::class)]
        public DataCollection|Optional|null $new_add_ons,
        public array|Optional|null $remove_products,
        #[DataCollectionOf(NewProductsRequestDTO::class)]
        public DataCollection|Optional|null $new_products,
    ) {
    }

    public static function rules(): array
    {
        $scheduleId = request()->route('scheduleId');

        return [
            'team_id' => 'numeric|exists:teams,id',
            'start_at' => ['nullable', 'date', 'after:now', new ValidStart()],
            'end_at' => 'nullable|date|after:start_at',
            'note' => 'nullable|string',
            'remove_add_ons' => 'nullable|array',
            'remove_add_ons.*' => ['integer', new ExistScheduleAddon($scheduleId)],
            'remove_products' => 'nullable|array',
            'remove_products.*' => ['integer', new ExistScheduleProduct($scheduleId)],
        ];
    }
}
