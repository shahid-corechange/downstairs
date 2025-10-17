<?php

namespace App\Http\Traits;

trait ArrayTrait
{
    /**
     * Filter array with given keys
     */
    public static function filterKeys(array $data, array $keys): array
    {
        return array_filter($data, function ($key) use ($keys) {
            return ! in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }
}
