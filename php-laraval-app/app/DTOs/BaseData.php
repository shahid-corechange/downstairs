<?php

namespace App\DTOs;

use App\Enums\TranslationEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Str;

class BaseData extends Data
{
    /**
     * Get all published meta of a model
     * and transform the keys to camelCase.
     *
     * @param  \App\Models\Model  $model
     * @return array<string, mixed>|null
     */
    public static function getModelMeta($model)
    {
        if (! method_exists($model, 'meta')) {
            return null;
        }

        return $model->meta->flatMap(fn ($meta) => [Str::camel($meta->key) => $meta->value])->toArray();
    }

    /**
     * Get all published meta of a model
     * and transform the keys to camelCase.
     *
     * @param  \App\Models\Model  $model
     * @return array<string, mixed>|null
     */
    public static function getTranslations($model)
    {
        if (! method_exists($model, 'translations')) {
            return null;
        }

        $results = [
            TranslationEnum::English() => [],
            TranslationEnum::Swedish() => [],
            TranslationEnum::Norwegian() => [],
        ];

        foreach ($model->translations as $translation) {
            $results[TranslationEnum::English()][$translation->key] = [
                'id' => $translation->id,
                'value' => $translation->en_US,
            ];
            $results[TranslationEnum::Swedish()][$translation->key] = [
                'id' => $translation->id,
                'value' => $translation->sv_SE,
            ];
            $results[TranslationEnum::Norwegian()][$translation->key] = [
                'id' => $translation->id,
                'value' => $translation->nn_NO,
            ];
        }

        return $results;
    }

    /**
     * Create and instance of the class from an associative array.
     * and transform it to array.
     *
     * @param  \App\Models\Model|array  $data
     * @param  string[]  $includes
     * @param  string[]  $excludes
     * @param  string[]  $onlys
     * @param  string[]  $excepts
     */
    public static function transformData(
        $data,
        array $includes = [],
        array $excludes = [],
        array $onlys = [],
        array $excepts = []
    ): array {
        $response = static::from($data)
            ->include(...$includes)
            ->exclude(...$excludes)
            ->only(...$onlys)
            ->except(...$excepts)
            ->toResponse(request());
        $data = json_decode($response->getContent(), true);

        return $data;
    }

    /**
     * Create and instance of the class from a collection.
     * and transform it to array.
     *
     * @param  \Illuminate\Support\Collection<array-key,\App\Models\Model>|
     * array<array-key,\App\Models\Model>  $collection
     * @param  string[]  $includes
     * @param  string[]  $excludes
     * @param  string[]  $onlys
     * @param  string[]  $excepts
     */
    public static function transformCollection(
        $collection,
        array $includes = [],
        array $excludes = [],
        array $onlys = [],
        array $excepts = []
    ): array {
        $response = static::collection($collection)
            ->include(...$includes)
            ->exclude(...$excludes)
            ->only(...$onlys)
            ->except(...$excepts)
            ->toResponse(request());
        $data = json_decode($response->getContent(), true);

        return $data;
    }

    /**
     * Allow fields to be used in `exclude` query string
     * return `null` to allow all fields.
     */
    public static function allowedRequestExcludes(): ?array
    {
        return null;
    }

    /**
     * Allow fields to be used in `include` query string
     * return `null` to allow all fields.
     */
    public static function allowedRequestIncludes(): ?array
    {
        return null;
    }

    /**
     * Allow fields to be used in `except` query string
     * return `null` to allow all fields.
     */
    public static function allowedRequestExcept(): ?array
    {
        return null;
    }

    /**
     * Allow fields to be used in `only` query string
     * return `null` to allow all fields.
     */
    public static function allowedRequestOnly(): ?array
    {
        return null;
    }

    /**
     * Check if the field value is optional.
     */
    public function isOptional(string $field): bool
    {
        if (! property_exists($this, $field)) {
            return false;
        }

        return $this->{$field} instanceof Optional;
    }

    /**
     * Check if the field value is not optional.
     */
    public function isNotOptional(string $field): bool
    {
        return ! $this->isOptional($field);
    }

    /**
     * Assing value to optional field.
     */
    public function assignIfOptional(string $field, mixed $value): void
    {
        if ($this->isOptional($field)) {
            $this->{$field} = $value;
        }
    }

    /**
     * Assing value to optional fields.
     *
     * @param  array<string, mixed>  $data
     */
    public function assignOptionalValues($data): void
    {
        foreach ($data as $field => $value) {
            $this->assignIfOptional($field, $value);
        }
    }
}
