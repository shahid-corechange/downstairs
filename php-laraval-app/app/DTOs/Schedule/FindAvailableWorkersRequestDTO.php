<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class FindAvailableWorkersRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('array|min:1')]
        public array|Optional $worker_ids,
        #[Rule('required|date')]
        public Carbon $start_at,
        #[Rule('required|date|after:start_at')]
        public Carbon $end_at,
    ) {
    }

    public static function rules(): array
    {
        return [
            'worker_ids.*' => 'numeric|exists:users,id',
        ];
    }
}
