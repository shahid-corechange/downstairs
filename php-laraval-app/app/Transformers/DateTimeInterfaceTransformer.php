<?php

namespace App\Transformers;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class DateTimeInterfaceTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        if (! $value instanceof \DateTimeInterface) {
            return $value;
        }

        $format = config('data.date_format');
        $carbon = Carbon::instance($value);

        if (request()->is('api/*')) {
            // Remove the timezone offset for API responses
            $format = str_replace('P', '', $format);
            $carbon->setTimezone('Europe/Stockholm');
        }

        return $carbon->format($format);
    }
}
