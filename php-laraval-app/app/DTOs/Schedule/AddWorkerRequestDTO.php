<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\Rules\Worker;
use App\Rules\WorkerNotAssignToSchedule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class AddWorkerRequestDTO extends BaseData
{
    public function __construct(
        public array $worker_ids,
    ) {
    }

    public static function rules(): array
    {
        $schedule = request()->route('schedule');

        return [
            'worker_ids' => 'required|array',
            'worker_ids.*' => [
                'numeric',
                'exists:users,id',
                new Worker(),
                new WorkerNotAssignToSchedule($schedule['id']),
            ],
        ];
    }
}
