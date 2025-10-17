<?php

namespace App\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class StringTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        if ($value && is_string($value)) {
            return trim($value);
        }

        return $value;
    }
}
