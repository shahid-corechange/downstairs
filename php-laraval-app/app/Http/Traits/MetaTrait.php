<?php

namespace App\Http\Traits;

trait MetaTrait
{
    // use ResponseTrait;

    /**
     * Get all published meta of a model.
     *
     * @param  \App\Models\Model  $model
     * @param  callable(\Kolossal\Multiplex\Meta $meta)|null  $callback
     * @return array<string, mixed>|null
     */
    public static function fromModel($model, callable $callback = null)
    {
        if (! method_exists($model, 'publishedMeta')) {
            return null;
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, \Kolossal\Multiplex\Meta> */
        $metas = $model['publishedMeta'];

        /**
         * return null if no meta, instead of empty array
         * to avoid ambiguous meta value in json response
         */
        if (count($metas) == 0) {
            return null;
        }

        return $metas->mapWithKeys(
            $callback ?: fn (\Kolossal\Multiplex\Meta $meta) => [$meta->key => $meta->value]
        )->toArray();
    }

    /**
     * Get payload meta and data
     *
     * @return array<string, array||null>
     */
    public function getPayload(array $dto)
    {
        $meta = array_key_exists('meta', $dto) ? array_keys_to_snake_case($dto['meta']) : null;
        $data = ArrayTrait::filterKeys($dto, ['meta']);

        return [$meta, $data];
    }
}
