<?php

namespace App\DTOs\Notification;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class NotificationGlobalSettingPayloadDTO extends BaseData
{
    public function __construct(
        public int $id,
        public string $key,
        public mixed $value,
    ) {
    }
}
