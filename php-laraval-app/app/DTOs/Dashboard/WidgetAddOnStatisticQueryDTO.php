<?php

namespace App\DTOs\Dashboard;

use App\DTOs\BaseData;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class WidgetAddOnStatisticQueryDTO extends BaseData
{
    public function __construct(
        public string|null|Optional $start_at,
        public string|null|Optional $end_at,
    ) {
        $this->start_at = $this->isOptional('start_at') ?
            now()->startOfMonth()->toDateTimeString() : $this->start_at;
        $this->end_at = $this->isOptional('end_at') ?
            now()->endOfMonth()->toDateTimeString() : $this->end_at;

        // if range between start_at and end_at is more than 1 year make it 1 year
        if (Carbon::parse($this->end_at)->diffInDays($this->start_at) > 365) {
            $this->end_at = Carbon::parse($this->start_at)->addYear()->toDateTimeString();
        }
    }

    public static function rules(): array
    {
        return [
            'start_at' => 'nullable|date|date_format:Y-m-d H:i:s',
            'end_at' => 'nullable|date|date_format:Y-m-d H:i:s|after:start_at',
        ];
    }
}
