<?php

namespace App\DTOs\ScheduleEmployee;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class EndScheduleEmployeeRequestDTO extends BaseData
{
    public function __construct(
        public float $end_latitude,
        public float $end_longitude,
        public string $end_ip,
        public array|Optional $completed_task_ids,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $description,
    ) {
    }

    public static function rules(): array
    {
        return [
            'end_latitude' => 'required|numeric',
            'end_longitude' => 'required|numeric',
            'end_ip' => 'required|string',
            'completed_task_ids' => 'array',
            'completed_task_ids.*' => 'numeric|exists:schedule_tasks,id',
            'description' => 'string',
        ];
    }
}
