<?php

namespace App\DTOs\Casts;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class CarbonCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): Carbon
    {
        try {
            $carbon = Carbon::parse($value);
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            throw ValidationException::withMessages([
                $property->name => [__('validation.date', ['attribute' => $property->name])],
            ]);
        }

        if (request()->is('api/*')) {
            $carbon = $carbon->shiftTimezone('Europe/Stockholm');
        }

        return $carbon->utc();
    }
}
