<?php

namespace App\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class SocialSecurityNumberTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        if ($value && is_string($value)) {
            return preg_replace('/\D/', '', $value);
        }

        return $value;
    }
}
