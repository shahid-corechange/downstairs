<?php

namespace App\DTOs\Notification;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
class NotificationSchedulePayloadDTO extends BaseData
{
    public function __construct(
        public Optional|int $id,
        public Optional|string $startAt,
        public Optional|string $endAt,
    ) {
    }
}
