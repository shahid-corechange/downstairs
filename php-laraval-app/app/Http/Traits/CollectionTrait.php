<?php

namespace App\Http\Traits;

trait CollectionTrait
{
    public static function keyToCamelCase(array $data): array
    {
        $result = array_map(function ($arr) {
            return array_keys_to_camel_case($arr);
        }, $data);

        return $result;
    }

    public static function keyToSnakeCase(array $data): array
    {
        $result = array_map(function ($arr) {
            return array_keys_to_snake_case($arr);
        }, $data);

        return $result;
    }
}
