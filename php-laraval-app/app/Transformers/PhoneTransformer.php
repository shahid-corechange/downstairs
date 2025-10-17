<?php

namespace App\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class PhoneTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        if ($value && is_string($value)) {
            return str_replace(['+', ' '], '', $value);
        }

        return $value;
    }
}
