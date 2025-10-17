<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class RescheduleRequestDTO extends BaseData
{
    public function __construct(
        public int $team_id,
        public string $start_at,
        public bool $is_notify,
    ) {
    }

    public static function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'start_at' => 'required|date|after:now',
            'is_notify' => 'required|boolean',
        ];
    }
}
