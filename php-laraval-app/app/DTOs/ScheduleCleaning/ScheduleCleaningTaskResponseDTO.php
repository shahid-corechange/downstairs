<?php

namespace App\DTOs\ScheduleCleaning;

use App\DTOs\BaseData;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\Models\ScheduleCleaningTask;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleCleaningTaskResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $customTaskId,
        public Lazy|null|int $scheduleCleaningId,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|bool $isCompleted,
        public Lazy|null|CustomTaskResponseDTO $customTask,
        public Lazy|null|ScheduleCleaningResponseDTO $schedule,
    ) {
    }

    public static function fromModel(ScheduleCleaningTask $scheduleCleaningTask): self
    {
        return new self(
            Lazy::create(fn () => $scheduleCleaningTask->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningTask->custom_task_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningTask->schedule_cleaning_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningTask->name)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningTask->description)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningTask->is_completed)->defaultIncluded(),
            Lazy::create(fn () => CustomTaskResponseDTO::from($scheduleCleaningTask->customTask)),
            Lazy::create(fn () => ScheduleCleaningResponseDTO::from($scheduleCleaningTask->schedule)),
        );
    }
}
