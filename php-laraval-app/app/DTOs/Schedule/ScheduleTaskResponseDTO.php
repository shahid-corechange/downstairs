<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\Models\ScheduleTask;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleTaskResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $customTaskId,
        public Lazy|null|int $scheduleId,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|bool $isCompleted,
        public Lazy|null|CustomTaskResponseDTO $customTask,
        public Lazy|null|ScheduleResponseDTO $schedule,
    ) {
    }

    public static function fromModel(ScheduleTask $scheduleTask): self
    {
        return new self(
            Lazy::create(fn () => $scheduleTask->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleTask->custom_task_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleTask->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleTask->name)->defaultIncluded(),
            Lazy::create(fn () => $scheduleTask->description)->defaultIncluded(),
            Lazy::create(fn () => $scheduleTask->is_completed)->defaultIncluded(),
            Lazy::create(fn () => CustomTaskResponseDTO::from($scheduleTask->customTask)),
            Lazy::create(fn () => ScheduleResponseDTO::from($scheduleTask->schedule)),
        );
    }
}
