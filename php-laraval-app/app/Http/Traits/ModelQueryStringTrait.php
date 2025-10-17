<?php

namespace App\Http\Traits;

use App\Enums\Api\QueryFilterEnum;
use App\Helpers\Database\DataPaginator;
use Artisan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Str;

trait ModelQueryStringTrait
{
    /**
     * The table schema.
     *
     * @var array<string,array<string,string>>
     */
    protected static array $tableSchema = [];

    /**
     * Boot the model query string trait for a model.
     *
     * @return void
     */
    public static function bootModelQueryStringTrait()
    {
        if (! file_exists(base_path('bootstrap/cache/table_schema.php'))) {
            Artisan::call('table:cache');
        }
    }

    /**
     * Load the table schema from the cache.
     */
    protected static function loadSchema()
    {
        if (count(static::$tableSchema) === 0) {
            static::$tableSchema = require base_path('bootstrap/cache/table_schema.php');
        }
    }

    /**
     * Check if the given column is exists in the table schema.
     */
    protected static function tableHasColumn(string $table, string $column): bool
    {
        return isset(static::$tableSchema[$table][$column]);
    }

    /**
     * Get the column type based on the given table and column.
     */
    protected static function getColumnType(string $table, string $column): string
    {
        if (! static::tableHasColumn($table, $column)) {
            return '';
        }

        return static::$tableSchema[$table][$column];
    }

    /**
     * Check if the given column is a JSON column.
     */
    protected static function isJSONColumn(string $table, string $column): bool
    {
        return static::getColumnType($table, $column) === 'json';
    }

    /**
     * Check if the given column is a date column.
     */
    protected static function isDateColumn(string $table, string $column): bool
    {
        return in_array(static::getColumnType($table, $column), ['date', 'datetime', 'timestamp']);
    }

    /**
     * Check if the given column is a boolean column.
     */
    protected static function isBooleanColumn(string $table, string $column): bool
    {
        return in_array(static::getColumnType($table, $column), ['bool', 'tinyint']);
    }

    /**
     * Filter based on the given criteria.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  string|string[]  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    protected static function filterByCriteria(
        $model,
        string $column,
        string $criteria,
        $value,
        string $boolean = 'and'
    ) {
        switch ($criteria) {
            case QueryFilterEnum::Equal():
            case QueryFilterEnum::Eq():
                return $model->where($model->qualifyColumn($column), '=', $value, boolean: $boolean);
            case QueryFilterEnum::NotEqual():
            case QueryFilterEnum::Neq():
                return $model->where($model->qualifyColumn($column), '!=', $value, boolean: $boolean);
            case QueryFilterEnum::GreaterThanOrEqual():
            case QueryFilterEnum::Gte():
                return $model->where($model->qualifyColumn($column), '>=', $value, boolean: $boolean);
            case QueryFilterEnum::LessThanOrEqual():
            case QueryFilterEnum::Lte():
                return $model->where($model->qualifyColumn($column), '<=', $value, boolean: $boolean);
            case QueryFilterEnum::GreaterThan():
            case QueryFilterEnum::Gt():
                return $model->where($model->qualifyColumn($column), '>', $value, boolean: $boolean);
            case QueryFilterEnum::LessThan():
            case QueryFilterEnum::Lt():
                return $model->where($model->qualifyColumn($column), '<', $value, boolean: $boolean);
            case QueryFilterEnum::Includes():
            case QueryFilterEnum::Like():
                return $model->where($model->qualifyColumn($column), 'LIKE', "%{$value}%", boolean: $boolean);
            case QueryFilterEnum::In():
                return $model->whereIn($model->qualifyColumn($column), $value, boolean: $boolean);
            case QueryFilterEnum::NotIn():
                return $model->whereNotIn($model->qualifyColumn($column), $value, boolean: $boolean);
            case QueryFilterEnum::Between():
                return $model->whereBetween($model->qualifyColumn($column), $value, boolean: $boolean);
            case QueryFilterEnum::Nullable():
                return $model->whereNull($model->qualifyColumn($column), boolean: $boolean, not: ! boolval($value));
            default:
                return $model;
        }
    }

    /**
     * Filter JSON column based on the given criteria.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  string|string[]  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    protected static function filterJSONByCriteria(
        $model,
        string $column,
        ?string $property,
        string $criteria,
        $value,
        string $boolean = 'and'
    ) {
        switch ($criteria) {
            case QueryFilterEnum::Includes():
            case QueryFilterEnum::Like():
                $jsonColumn = $property ? "$column->'$.$property'" : $column;

                return $model->whereRaw("LOWER($jsonColumn) LIKE ?", '%'.strtolower($value).'%', boolean: $boolean);
            case QueryFilterEnum::In():
                $jsonColumn = $property ? "$column.$property" : $column;

                return $model->whereJsonContains($column, $value, boolean: $boolean);
            default:
                return static::filterByCriteria($model, $column, $criteria, $value, $boolean);
        }
    }

    /**
     * Filter based on the given meta criteria.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  string|string[]  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    protected static function filterByMetaCriteria(
        $model,
        string $column,
        string $criteria,
        $value,
        string $boolean = 'and'
    ) {
        switch ($criteria) {
            case QueryFilterEnum::Equal():
            case QueryFilterEnum::Eq():
                return $model->whereMeta($model->qualifyColumn($column), '=', $value, boolean: $boolean);
            case QueryFilterEnum::NotEqual():
            case QueryFilterEnum::Neq():
                return $model->whereMeta($model->qualifyColumn($column), '!=', $value, boolean: $boolean);
            case QueryFilterEnum::GreaterThanOrEqual():
            case QueryFilterEnum::Gte():
                return $model->whereMeta($model->qualifyColumn($column), '>=', $value, boolean: $boolean);
            case QueryFilterEnum::LessThanOrEqual():
            case QueryFilterEnum::Lte():
                return $model->whereMeta($model->qualifyColumn($column), '<=', $value, boolean: $boolean);
            case QueryFilterEnum::GreaterThan():
            case QueryFilterEnum::Gt():
                return $model->whereMeta($model->qualifyColumn($column), '>', $value, boolean: $boolean);
            case QueryFilterEnum::LessThan():
            case QueryFilterEnum::Lt():
                return $model->whereMeta($model->qualifyColumn($column), '<', $value, boolean: $boolean);
            case QueryFilterEnum::Includes():
            case QueryFilterEnum::Like():
                return $model->whereRawMeta($model->qualifyColumn($column), 'LIKE', "%{$value}%", boolean: $boolean);
            case QueryFilterEnum::In():
                return $model->whereMetaIn($model->qualifyColumn($column), $value, boolean: $boolean);
            case QueryFilterEnum::NotIn():
                return $model->whereNot(
                    function ($query) use ($model, $column, $value) {
                        $query->whereMetaIn($model->qualifyColumn($column), $value);
                    },
                    boolean: $boolean
                );
            case QueryFilterEnum::Between():
                return $model->where(
                    function ($query) use ($model, $column, $value) {
                        $query->whereMeta($model->qualifyColumn($column), '>=', $value)
                            ->whereMeta($model->qualifyColumn($column), '<=', $value);
                    },
                    boolean: $boolean
                );
            case QueryFilterEnum::Nullable():
                if (boolval($value)) {
                    return $model->whereMetaEmpty($model->qualifyColumn($column), boolean: $boolean);
                }

                return $model->whereMetaNotEmpty($model->qualifyColumn($column), boolean: $boolean);
            default:
                return $model;
        }
    }

    /**
     * Filter based on the given criteria but using raw query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  string|string[]  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    protected static function filterByRawQuery(
        $model,
        string $rawColumn,
        string $criteria,
        $value,
        string $boolean = 'and'
    ) {
        switch ($criteria) {
            case QueryFilterEnum::Equal():
            case QueryFilterEnum::Eq():
                return $model->whereRaw($rawColumn.' = ?', [$value], boolean: $boolean);
            case QueryFilterEnum::NotEqual():
            case QueryFilterEnum::Neq():
                return $model->whereRaw($rawColumn.' != ?', [$value], boolean: $boolean);
            case QueryFilterEnum::GreaterThanOrEqual():
            case QueryFilterEnum::Gte():
                return $model->whereRaw($rawColumn.' >= ?', [$value], boolean: $boolean);
            case QueryFilterEnum::LessThanOrEqual():
            case QueryFilterEnum::Lte():
                return $model->whereRaw($rawColumn.' <= ?', [$value], boolean: $boolean);
            case QueryFilterEnum::GreaterThan():
            case QueryFilterEnum::Gt():
                return $model->whereRaw($rawColumn.' > ?', [$value], boolean: $boolean);
            case QueryFilterEnum::LessThan():
            case QueryFilterEnum::Lt():
                return $model->whereRaw($rawColumn.' < ?', [$value], boolean: $boolean);
            case QueryFilterEnum::Includes():
            case QueryFilterEnum::Like():
                return $model->whereRaw($rawColumn.' LIKE ?', ['%'.$value.'%'], boolean: $boolean);
            case QueryFilterEnum::In():
                $placeholders = implode(',', array_fill(0, count($value), '?'));

                return $model->whereRaw($rawColumn.' IN ('.$placeholders.')', $value, boolean: $boolean);
            case QueryFilterEnum::NotIn():
                $placeholders = implode(',', array_fill(0, count($value), '?'));

                return $model->whereRaw($rawColumn.' NOT IN ('.$placeholders.')', $value, boolean: $boolean);
            case QueryFilterEnum::Between():
                return $model->whereRaw($rawColumn.' BETWEEN ? AND ?', $value, boolean: $boolean);
            case QueryFilterEnum::Nullable():
                $sqlCriteria = boolval($value) ? 'IS NULL' : 'IS NOT NULL';

                return $model->whereRaw($rawColumn.' '.$sqlCriteria, boolean: $boolean);
            default:
                return $model;
        }
    }

    /**
     * Get the opposite criteria to filter has many relationship.
     */
    protected static function getHasManyFilterCriteria(string $criteria, bool $exact): string
    {
        // Return the opposite criteria on non-exact mode and the criteria is not equal.
        if (! $exact && in_array($criteria, [QueryFilterEnum::NotEqual(), QueryFilterEnum::Neq()])) {
            return QueryFilterEnum::Equal();
        }

        // Return the opposite criteria on exact mode.
        switch ($criteria) {
            case QueryFilterEnum::Equal():
            case QueryFilterEnum::Eq():
                return QueryFilterEnum::NotEqual();
            case QueryFilterEnum::NotEqual():
            case QueryFilterEnum::Neq():
                return QueryFilterEnum::Equal();
            case QueryFilterEnum::GreaterThanOrEqual():
            case QueryFilterEnum::Gte():
                return QueryFilterEnum::LessThan();
            case QueryFilterEnum::LessThanOrEqual():
            case QueryFilterEnum::Lte():
                return QueryFilterEnum::GreaterThan();
            case QueryFilterEnum::GreaterThan():
            case QueryFilterEnum::Gt():
                return QueryFilterEnum::LessThanOrEqual();
            case QueryFilterEnum::LessThan():
            case QueryFilterEnum::Lt():
                return QueryFilterEnum::GreaterThanOrEqual();
            default:
                return $criteria;
        }
    }

    /**
     * Transform the value based on the column type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     */
    protected static function transformValue($model, string $column, $value, $fromMeta = false, $fromJson = false)
    {
        if (gettype($value) === 'array') {
            $newValue = [];

            foreach ($value as $item) {
                $newValue[] = static::transformValue($model, $column, $item, $fromMeta, $fromJson);
            }

            return $newValue;
        }

        if (gettype($value) !== 'string') {
            return $value;
        }

        if (gettype($value) === 'string' && strtolower($value) === 'null') {
            return null;
        }

        if ($fromMeta || $fromJson) {
            $newValue = json_decode($value);

            if (in_array(gettype($newValue), ['NULL', 'array', 'object'])) {
                return $value;
            }

            return $newValue;
        }

        if (static::isBooleanColumn($model->getModel()->getTable(), $column)) {
            return strtolower($value) === 'true' || strtolower($value) === '1' ? true : false;
        } elseif (static::isDateColumn($model->getModel()->getTable(), $column)) {
            if (request()->is('api/*')) {
                try {
                    $carbon = Carbon::parse($value);

                    return $carbon->shiftTimezone('Europe/Stockholm')->toISOString();
                } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                    return $value;
                }
            }
        }

        return $value;
    }

    /**
     * Get column name based on the given alias.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     */
    protected static function getColumnNameByAlias($model, string $alias): string
    {
        $aliases = $model->getModel()->aliases;

        if (array_key_exists($alias, $aliases ?? [])) {
            return $aliases[$alias];
        }

        return $alias;
    }

    /**
     * Filter the result based on relationship condition.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  string[]  $columns
     * @param  string|string[]  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    protected static function filterByRelationship(
        $model,
        array $columns,
        string $criteria,
        $value,
        $hasMany = false,
        $exact = false,
        string $boolean = 'and',
    ) {
        $column = array_shift($columns);
        $rawColumn = '';

        // Ignore if the column is hidden
        if (in_array($column, $model->getModel()->getHidden())) {
            return $model;
        }

        // Handle JSON column.
        if (in_array(count($columns), [0, 1]) &&
            static::isJSONColumn($model->getModel()->getTable(), Str::snake($column))) {
            $column = Str::snake($column);
            $property = null;

            if (count($columns) === 1) {
                $property = array_shift($columns);
            }

            $value = static::transformValue($model, $column, $value, fromJson: true);

            return static::filterJSONByCriteria($model, $column, $property, $criteria, $value, $boolean);
        }

        if (count($columns) > 0) {
            // Check if the relationship exists.
            if (! method_exists($model->getModel(), $column)) {
                $column = static::getColumnNameByAlias($model, $column);

                if (! method_exists($model->getModel(), $column)) {
                    return $model;
                }
            }

            /**
             * Check if the relationship is has many.
             * Use whereDoesntHave() instead of whereHas() if the criteria is not equal
             * or running in exact mode.
             **/
            $relationshipMethod = new \ReflectionMethod($model->getModel(), $column);
            $relationshipType = $relationshipMethod->getReturnType();

            if (! $hasMany &&
                (
                    (! $exact && in_array($criteria, [QueryFilterEnum::NotEqual(), QueryFilterEnum::Neq()])) ||
                    ($exact && ! in_array($criteria, [
                        QueryFilterEnum::Includes(),
                        QueryFilterEnum::Like(),
                        QueryFilterEnum::In(),
                        QueryFilterEnum::NotIn(),
                        QueryFilterEnum::Between(),
                    ]))
                ) &&
                $relationshipType instanceof \ReflectionNamedType &&
                in_array($relationshipType->getName(), [
                    HasMany::class,
                    BelongsToMany::class,
                    MorphToMany::class,
                ])
            ) {
                $model->whereHas(
                    $column,
                    function (Builder $model) use ($columns, $criteria, $value, $hasMany, $boolean) {
                        static::filterByRelationship($model, $columns, $criteria, $value, $hasMany, false, $boolean);
                    }
                );

                return $model->whereDoesntHave(
                    $column,
                    function (Builder $model) use ($columns, $criteria, $value, $exact, $boolean) {
                        static::filterByRelationship($model, $columns, $criteria, $value, true, $exact, $boolean);
                    }
                );
            }

            if ($boolean === 'or' && static::isPolymorphic($relationshipType->getName())) {
                // we need to reverse the boolean on polymorphic relationship when the boolean is or
                return $model->orWhereHas(
                    $column,
                    function (Builder $model) use ($columns, $criteria, $value, $hasMany, $exact) {
                        static::filterByRelationship($model, $columns, $criteria, $value, $hasMany, $exact, 'and');
                    }
                );
            }

            return $model->whereHas(
                $column,
                function (Builder $model) use ($columns, $criteria, $value, $hasMany, $exact, $boolean) {
                    static::filterByRelationship($model, $columns, $criteria, $value, $hasMany, $exact, $boolean);
                }
            );
        }

        // Check if the column exists.
        if (! static::tableHasColumn($model->getModel()->getTable(), $column)) {
            $oldColumn = $column;
            $column = static::getColumnNameByAlias($model, $column);

            // Skip if the column is not found.
            if ($column === $oldColumn || ! static::tableHasColumn($model->getModel()->getTable(), $column)) {
                $accessors = $model->getModel()->getAppends();
                $methodName = Str::camel($column);

                // Skip if the column is not an accessor or the accessor method is not found.
                if (! (in_array($column, $accessors) && method_exists($model->getModel(), $methodName))) {
                    return $model;
                }

                // Get the raw expressions from the accessor method.
                $rawExpressions = $model->getModel()->$methodName();

                if (is_string($rawExpressions)) {
                    $rawColumn = $rawExpressions;
                } elseif (is_array($rawExpressions)) {
                    $rawColumn = $rawExpressions['column'];
                    $rawJoins = isset($rawExpressions['joins']) ? $rawExpressions['joins'] : [];

                    foreach ($rawJoins as $join) {
                        $model->getQuery()->join(...$join);
                    }
                }
            }
        }

        if ($hasMany) {
            $criteria = static::getHasManyFilterCriteria($criteria, $exact);
        }

        if ($rawColumn) {
            return static::filterByRawQuery($model, $rawColumn, $criteria, $value, $boolean);
        }

        $value = static::transformValue($model, $column, $value);

        return static::filterByCriteria($model, $column, $criteria, $value, $boolean);
    }

    /**
     * Filter the result based on the meta condition.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  string[]  $columns
     * @param  string|string[]  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    protected static function filterByMeta(
        $model,
        string $column,
        string $criteria,
        $value,
        $boolean = 'and'
    ) {
        // Check if model has meta.
        if (! method_exists($model->getModel(), 'hasMeta')) {
            return $model;
        }

        $value = static::transformValue($model, $column, $value, true);

        return static::filterByMetaCriteria($model, $column, $criteria, $value, $boolean);
    }

    /**
     * Filter the result based on the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    public static function filter(
        array $query,
        $model = null,
        $exact = false,
        $show = 'active',
        $boolean = 'and'
    ) {
        if (! $model) {
            /** @var \Illuminate\Database\Eloquent\Builder */
            $model = static::query();
        }

        /**
         * What data will be get.
         * The default is active or not soft deleted data.
         * All for get all data.
         * Deleted for get only soft deleted data.
         **/
        if ($show === 'all' && method_exists($model->getModel(), 'softDeleted')) {
            $model->withTrashed();
        } elseif ($show === 'deleted' && method_exists($model->getModel(), 'softDeleted')) {
            $model->onlyTrashed();
        }

        foreach ($query as $filter) {
            if (count($filter['keys']) == 0) {
                continue;
            }

            // Check if the filter is for meta.
            // Example: meta.color.eq=red
            if (count($filter['keys']) == 2 && $filter['keys'][0] == 'meta') {
                static::filterByMeta($model, $filter['keys'][1], $filter['criteria'], $filter['value'], $boolean);

                continue;
            }

            static::filterByRelationship(
                $model,
                $filter['keys'],
                $filter['criteria'],
                $filter['value'],
                exact: $exact,
                boolean: $boolean,
            );
        }

        return $model;
    }

    /**
     * Get relation local key.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     */
    protected static function getRelationLocalKey($relation): string
    {
        $relationType = get_class($relation);

        switch ($relationType) {
            case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                return $relation->getForeignKeyName();
            case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                return $relation->getQualifiedRelatedPivotKeyName();
            case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
            case 'Illuminate\Database\Eloquent\Relations\HasOne':
                return $relation->getQualifiedParentKeyName();
            default:
                return '';
        }
    }

    /**
     * Get relation foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     */
    protected static function getRelationForeignKey($relation): string
    {
        $relationType = get_class($relation);

        switch ($relationType) {
            case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                return $relation->getQualifiedOwnerKeyName();
            case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                return $relation->getQualifiedRelatedKeyName();
            case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
            case 'Illuminate\Database\Eloquent\Relations\HasOne':
                return $relation->getQualifiedForeignKeyName();
            default:
                return '';
        }
    }

    /**
     * Check if the relationship is many to many.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     */
    protected static function isManyToMany($relation): bool
    {
        $relationType = get_class($relation);

        return in_array(
            $relationType,
            [BelongsToMany::class, MorphToMany::class]
        );
    }

    /**
     * Check if the relationship is polymorphic.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation|string  $relation
     */
    protected static function isPolymorphic($relation): bool
    {
        $relationType = is_string($relation) ? $relation : get_class($relation);

        return in_array(
            $relationType,
            [MorphTo::class]
        );
    }

    /**
     * Get join foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     */
    protected static function getJoinForeignKey($relation): ?string
    {
        $relationType = get_class($relation);

        if ($relationType !== 'Illuminate\Database\Eloquent\Relations\BelongsToMany') {
            return null;
        }

        return $relation->getQualifiedForeignPivotKeyName();
    }

    /**
     * Sort the result based on relationship condition.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $model
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     * @param  string[]  $columns
     * @return \Illuminate\Database\Query\Builder|null
     *
     * @static
     */
    protected static function sortByRelationship($model, $relation, array $columns, string $direction)
    {
        $relationLocalKey = static::getRelationLocalKey($relation);
        $relationForeignKey = static::getRelationForeignKey($relation);

        if (! $relationLocalKey || ! $relationForeignKey) {
            return null;
        }

        $column = array_shift($columns);

        // Ignore if the column is hidden
        if (in_array($column, $model->getModel()->getHidden())) {
            return null;
        }

        // Handle JSON column.
        if (count($columns) === 1 &&
            static::isJSONColumn($model->getModel()->getTable(), Str::snake($column))) {
            $property = array_shift($columns);
            $column = Str::snake($column);

            return $model->select("$column->$property")
                ->orderByRaw("$column->'$.$property' $direction")
                ->limit(1);
        }

        if (count($columns) > 0) {
            // Check if the relationship exists.
            if (! method_exists($model->getModel(), $column)) {
                $column = static::getColumnNameByAlias($model, $column);

                if (! method_exists($model->getModel(), $column)) {
                    return null;
                }
            }

            $subRelation = $model->getModel()->{$column}();
            $orderBy = static::sortByRelationship(
                $subRelation->getModel(),
                $subRelation,
                $columns,
                $direction
            );

            if (! $orderBy) {
                return null;
            }

            $query = $model->select(static::getRelationLocalKey($subRelation));

            if (static::isManyToMany($subRelation)) {
                $query->join(
                    $subRelation->getTable(),
                    $subRelation->getQualifiedParentKeyName(),
                    '=',
                    static::getJoinForeignKey($subRelation)
                );
            }

            return $query->whereColumn($relationLocalKey, $relationForeignKey)
                ->orderBy($orderBy, $direction)
                ->limit(1);
        }

        // Check if the column exists.
        if (! static::tableHasColumn($model->getModel()->getTable(), $column)) {
            $column = static::getColumnNameByAlias($model, $column);

            if (! static::tableHasColumn($model->getModel()->getTable(), $column)) {
                return null;
            }
        }

        $query = $model->select($column);

        if (static::isManyToMany($relation)) {
            $query->join(
                $relation->getTable(),
                $relation->getQualifiedParentKeyName(),
                '=',
                static::getJoinForeignKey($relation)
            );
        }

        return $query->whereColumn($relationLocalKey, $relationForeignKey)
            ->orderBy($column, $direction)
            ->limit(1);
    }

    /**
     * Sort the result based on the given query.
     *
     * @param  string[]  $query
     * @param  \Illuminate\Database\Eloquent\Builder|\App\Models\Model  $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    public static function sort(array $query, $model)
    {
        foreach ($query as $key => $direction) {
            if (! str_contains($key, '.')) {
                if (in_array($key, $model->getModel()->getHidden())) {
                    continue;
                }

                if (! static::tableHasColumn($model->getModel()->getTable(), $key)) {
                    $key = static::getColumnNameByAlias($model, $key);

                    if (! static::tableHasColumn($model->getModel()->getTable(), $key)) {
                        continue;
                    }
                }

                $model->orderBy($key, $direction);
            } else {
                $columns = explode('.', $key);
                $column = array_shift($columns);

                // Handle JSON column.
                if (count($columns) === 1 &&
                    static::isJSONColumn($model->getModel()->getTable(), Str::snake($column))) {
                    $property = array_shift($columns);
                    $column = Str::snake($column);

                    $model->orderByRaw("$column->'$.$property' $direction");

                    continue;
                }

                if (! method_exists($model->getModel(), $column)) {
                    $column = static::getColumnNameByAlias($model, $column);

                    if (! method_exists($model->getModel(), $column)) {
                        continue;
                    }
                }

                $query = static::sortByRelationship(
                    $model->getModel()->{$column}()->getModel(),
                    $model->getModel()->{$column}(),
                    $columns,
                    $direction
                );

                if (! $query) {
                    continue;
                }

                $model->orderBy($query, $direction);
            }
        }

        return $model;
    }

    /**
     * Merge the given fields and fields from "only" query string.
     *
     * @param  string[]  $fields
     * @return string[]
     */
    protected static function mergeFields(array $fields): array
    {
        $onlyQuery = request()->get('only') ? explode(',', request()->get('only', '')) : [];

        return array_unique([...$fields, ...$onlyQuery]);
    }

    /**
     * Check if the given field is an accessor.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     */
    protected static function isAccessor($model, string $field): bool
    {
        return in_array(Str::snake($field), $model->getAppends());
    }

    /**
     * Get fields that is required by the accessor.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     * @return string[]
     */
    protected static function getAccessorFields($model, string $accessor): array
    {
        $field = Str::snake($accessor);

        if (! isset($model->accessorsFields[$field])) {
            return [];
        }

        return $model->accessorsFields[$field];
    }

    /**
     * Get relations that is required by the accessor.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     * @return array<string,string[]>
     */
    protected static function getAccessorRelations($model, string $accessor): array
    {
        $field = Str::snake($accessor);

        if (! isset($model->accessorsRelations[$field])) {
            return [];
        }

        return $model->accessorsRelations[$field];
    }

    /**
     * Resolve fields and relationships that are required by the accessor.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     * @param  string[]  $fields
     * @param  array<string,string[]>  $relations
     * @return bool
     */
    protected static function resolveAccessor(
        $model,
        &$fields,
        &$relations,
        string $relation,
        string $column,
        bool $resolveFields = true,
    ) {
        $relation = $relation ?: '.';
        $column = Str::snake($column);

        if (static::isAccessor($model, $column)) {
            $accessorRelations = static::getAccessorRelations($model, $column);

            if ($resolveFields) {
                $accessorFields = static::getAccessorFields($model, $column);
                $relations[$relation] = array_merge(
                    $relations[$relation],
                    static::getQualifiedColumnsName($model, $accessorFields),
                );
            }

            foreach ($accessorRelations as $accessorRelation => $accessorRelationFields) {
                $newAccessorRelation = $relation === '.' ?
                    $accessorRelation : $relation.'.'.$accessorRelation;
                $relationFields = array_map(
                    fn (string $field) => $newAccessorRelation.'.'.$field,
                    $accessorRelationFields
                );
                $fields = array_merge($fields, $relationFields);
            }

            return true;
        }

        return false;
    }

    /**
     * Extract the fields for selecting fields on source model.
     *
     * @param  string[]  $relations
     * @return string[]
     */
    protected static function extractSourceFields(array &$relations): array
    {
        // Get the source model fields. It is the first element of the fields.
        $sourceModel = array_shift($relations);
        // The source model relation is named as '.'.
        $fields = str_replace('.:', '', $sourceModel);

        return explode(',', $fields);
    }

    /**
     * Get name of the column prefixed with the table name.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     */
    protected static function getQualifiedColumnName($model, string $column): string
    {
        return $model->getTable().'.'.$column;
    }

    /**
     * Get name of the columns prefixed with the table name.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     * @param  string[]  $columns
     * @return string[]
     */
    protected static function getQualifiedColumnsName($model, array $columns): array
    {
        return array_map(
            fn (string $column) => static::getQualifiedColumnName($model, $column),
            $columns
        );
    }

    /**
     * Resolve the model's relationship based on the "only" query.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     * @param  string[]  $columns
     * @param  string[]  $fields
     * @param  array<string,string[]>  $relations
     * @param  array<string,array<string,string[]>>  $polymorphicRelations
     */
    protected static function resolveRelationships(
        $model,
        array $columns,
        array &$fields,
        array &$relations,
        array &$polymorphicRelations,
        string $relation = '',
    ) {
        $relationModel = clone $model;

        for ($i = 0; $i < count($columns); $i++) {
            $column = $columns[$i];

            if ($i === count($columns) - 1) {
                $column = static::getColumnNameByAlias($relationModel, Str::snake($column));
                // Set the relation to source model if there is no relation.
                $relation = $relation ?: '.';

                if (static::resolveAccessor(
                    $relationModel,
                    $fields,
                    $relations,
                    $relation,
                    $column,
                )) {
                    break;
                }

                // Check if the column is exists in the table schema.
                if (! isset(static::$tableSchema[$relationModel->getTable()][$column])) {
                    break;
                }

                $relations[$relation][] = static::getQualifiedColumnName($relationModel, $column);
                break;
            }

            $column = static::getColumnNameByAlias($relationModel, Str::camel($column));

            if (! $relationModel->isRelation($column)) {
                $column = static::getColumnNameByAlias($relationModel, Str::snake($column));
                $fullColumn = $relation ? $relation.'.'.$column : $column;

                // Remove the fields that are prefixed with the full column name
                // because it is already resolved.
                $fields = array_filter($fields, fn ($field) => ! str_starts_with($field, $fullColumn.'.'));

                static::resolveAccessor(
                    $relationModel,
                    $fields,
                    $relations,
                    $relation,
                    $column
                );
                break;
            }

            $relationshipMethod = new \ReflectionMethod($relationModel, $column);
            $relationshipType = $relationshipMethod->getReturnType()->getName();

            // Skip if the relationship type is not a relation.
            if (! is_subclass_of($relationshipType, Relation::class)) {
                break;
            }

            /** @var \Illuminate\Database\Eloquent\Relations\Relation */
            $relationship = $relationModel->{$column}();
            $newRelation = $relation ? $relation.'.'.$column : $column;

            if (static::isPolymorphic($relationship)) {
                if (! isset($polymorphicRelations[$newRelation])) {
                    $polymorphicRelations[$newRelation] = [];

                    $qualifiedForeignKey = $relationship->getQualifiedForeignKeyName();
                    $foreignKey = explode('.', $qualifiedForeignKey);
                    $qualifiedMorphType = $foreignKey[0].'.'.$relationship->getMorphType();

                    if (! $relation && $foreignKey[0] === $model->getTable()) {
                        $relations['.'][] = $qualifiedForeignKey;
                        $relations['.'][] = $qualifiedMorphType;
                    } elseif ($foreignKey[0] === $relationModel->getTable()) {
                        $relations[$relation][] = $qualifiedForeignKey;
                        $relations[$relation][] = $qualifiedMorphType;
                    } else {
                        $relations[$newRelation][] = $qualifiedForeignKey;
                        $relations[$newRelation][] = $qualifiedMorphType;
                    }
                }

                static::resolvePolymorphicRelationships(
                    $relationModel,
                    array_slice($columns, $i),
                    $fields,
                    $polymorphicRelations[$newRelation],
                    $relation,
                );
                break;
            }

            if (! isset($relations[$newRelation])) {
                if ($column === 'meta') {
                    $relations[$newRelation] = [];

                    return;
                }

                $relatedModel = $relationship->getRelated();
                $relations[$newRelation] = static::getQualifiedColumnsName(
                    $relatedModel,
                    $relatedModel->includes ?? [],
                );

                if (! static::isManyToMany($relationship)) {
                    $qualifiedForeignKey = $relationship->getQualifiedForeignKeyName();
                    $foreignKey = explode('.', $qualifiedForeignKey);

                    if (! $relation && $foreignKey[0] === $model->getTable()) {
                        $relations['.'][] = $qualifiedForeignKey;
                    } elseif ($foreignKey[0] === $relationModel->getTable()) {
                        $relations[$relation][] = $qualifiedForeignKey;
                    } else {
                        $relations[$newRelation][] = $qualifiedForeignKey;

                        if ($relationship instanceof MorphMany) {
                            $relations[$newRelation][] = $foreignKey[0].'.'.$relationship->getMorphType();
                        }
                    }
                }
            }

            $relationModel = $relationship->getRelated();
            $relation = $newRelation;
        }
    }

    /**
     * Resolve the model's polymorphic relationship based on the "only" query.
     *
     * @param  \Illuminate\Database\Eloquent\Model|static  $model
     * @param  string[]  $columns
     * @param  string[]  $fields
     * @param  array<string,array<string,string[]>>  $polymorphicRelations
     */
    protected static function resolvePolymorphicRelationships(
        $model,
        array $columns,
        array &$fields,
        array &$polymorphicRelations,
        string $relation = ''
    ) {
        // Skip if columns has no relations
        if (count($columns) < 2) {
            return;
        }

        $polymorphField = static::getColumnNameByAlias($model, $columns[0]);

        $relationModels = array_reduce(
            $model->relationsMorphMap[$polymorphField],
            fn ($carry, $item) => [...$carry, $item => $item::query()->getModel()],
            []
        );
        $relation = $relation ? $relation.'.'.$polymorphField : $polymorphField;

        foreach ($relationModels as $modelCls => $relationModel) {
            for ($i = 1; $i < count($columns); $i++) {
                $column = $columns[$i];

                if ($i === count($columns) - 1) {
                    static::resolveAccessor(
                        $relationModel,
                        $fields,
                        $polymorphicRelations,
                        $relation,
                        $column,
                        resolveFields: false,
                    );
                    break;
                }

                $column = static::getColumnNameByAlias($relationModel, Str::camel($column));

                if (! $relationModel->isRelation($column)) {
                    $column = static::getColumnNameByAlias($relationModel, Str::snake($column));
                    $fullColumn = $relation ? $relation.'.'.$column : $column;

                    // Remove the fields that are prefixed with the full column name
                    // because it is already resolved.
                    $fields = array_filter($fields, fn ($field) => ! str_starts_with($field, $fullColumn.'.'));

                    static::resolveAccessor(
                        $relationModel,
                        $fields,
                        $polymorphicRelations,
                        $relation,
                        $column,
                        resolveFields: false,
                    );
                    break;
                }

                $relationshipMethod = new \ReflectionMethod($relationModel, $column);
                $relationshipType = $relationshipMethod->getReturnType()->getName();

                // Skip if the relationship type is not a relation.
                if (! is_subclass_of($relationshipType, Relation::class)) {
                    break;
                }

                /** @var \Illuminate\Database\Eloquent\Relations\Relation */
                $relationship = $relationModel->{$column}();
                $newRelation = $relation.'.'.$column;

                if (! isset($polymorphicRelations[$modelCls])) {
                    $polymorphicRelations[$modelCls] = [];
                }

                if (! isset($polymorphicRelations[$modelCls][$newRelation])) {
                    $relatedModel = $relationship->getRelated();
                    $polymorphicRelations[$modelCls][$newRelation] = static::getQualifiedColumnsName(
                        $relatedModel,
                        $relatedModel->includes ?? [],
                    );

                    if ($relationship instanceof HasMany) {
                        $qualifiedForeignKey = $relationship->getQualifiedForeignKeyName();
                        $polymorphicRelations[$modelCls][$newRelation][] = $qualifiedForeignKey;
                    } elseif ($relationship instanceof MorphMany) {
                        $qualifiedForeignKey = $relationship->getQualifiedForeignKeyName();
                        $qualifiedMorphType = static::getQualifiedColumnName(
                            $relatedModel,
                            $relationship->getMorphType()
                        );

                        $polymorphicRelations[$modelCls][$newRelation][] = $qualifiedForeignKey;
                        $polymorphicRelations[$modelCls][$newRelation][] = $qualifiedMorphType;
                    }
                }

                if (static::isPolymorphic($relationship)) {
                    // There might be a case in the future for nested polymorphic relations.
                    // For example: schedule.scheduleable (from scheduleEmployees model)
                    // But for now we can just skip it.
                    break;
                }

                static::resolveRelationships(
                    $relationship->getRelated(),
                    array_slice($columns, $i + 1),
                    $fields,
                    $polymorphicRelations[$modelCls],
                    $polymorphicRelations[$modelCls],
                    // We need to pass the full relation name
                    // because if the last column is an accessor,
                    // the fields will be added to the fields array
                    // based on the full relation name.
                    $newRelation,
                );
                break;
            }
        }
    }

    /**
     * Apply polymorphic relations to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $query
     * @param array<string,array<string,array<string,string[]>> $relations
     */
    protected static function applyPolymorphicRelations($query, array $relations)
    {
        foreach ($relations as $polymorphRelation => $modelsRelations) {
            $query->with([
                $polymorphRelation => function (MorphTo $morphTo) use ($polymorphRelation, $modelsRelations) {
                    $morphWith = array_reduce(
                        array_keys($modelsRelations),
                        function ($carry, $model) use ($polymorphRelation, $modelsRelations) {
                            $carry[$model] = array_map(
                                function ($key, $columns) use ($polymorphRelation) {
                                    $realKey = str_replace("$polymorphRelation.", '', $key);
                                    $uniqueColumns = array_unique($columns);

                                    if (count($uniqueColumns) > 0) {
                                        return $realKey.':'.implode(',', $uniqueColumns);
                                    }

                                    return $realKey;
                                },
                                array_keys($modelsRelations[$model]),
                                array_values($modelsRelations[$model])
                            );

                            return $carry;
                        },
                        []
                    );

                    $morphTo->morphWith($morphWith);
                },
            ]);
        }
    }

    /**
     * Resolve fields that is needed to be selected and relations that is needed
     * to be eager loaded based on the given fields.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|static  $query
     * @param  string[]  $fields
     */
    protected static function resolveFieldsAndRelations($query, array $fields)
    {
        $relations = [
            '.' => static::getQualifiedColumnsName(
                $query->getModel(),
                $query->getModel()->includes ?? [],
            ),
        ];
        $polymorphicRelations = [];

        while (count($fields) > 0) {
            $field = array_shift($fields);
            $columns = explode('.', $field);

            static::resolveRelationships(
                $query->getModel(),
                $columns,
                $fields,
                $relations,
                $polymorphicRelations
            );
        }

        $formattedRelations = array_map(
            function ($key, $columns) {
                $uniqueColumns = array_unique($columns);

                return count($uniqueColumns) > 0 ? $key.':'.implode(',', $uniqueColumns) : $key;
            },
            array_keys($relations),
            array_values($relations)
        );
        $sourceFields = static::extractSourceFields($formattedRelations);

        if (count($sourceFields) > 0) {
            $query->select($sourceFields);
        }

        if (count($formattedRelations) > 0) {
            $query->with($formattedRelations);
        }

        static::applyPolymorphicRelations($query, $polymorphicRelations);
    }

    /**
     * Apply a filter, sort and paginate operation
     * based on the given queries.
     *
     * @param  string[]  $fields
     * @return \App\Helpers\Database\DataPaginator<static>
     *
     * @static
     */
    public static function applyFilterSortAndPaginate(
        array $queries,
        array $fields = [],
    ) {
        static::loadSchema();

        $model = static::filter(
            $queries['filter']['filters'],
            exact: $queries['filter']['exact'],
            show: $queries['show']
        );

        foreach ($queries['filter']['or_filters'] as $orFilters) {
            $model = $model->where(function ($query) use ($queries, $orFilters) {
                static::filter(
                    $orFilters,
                    $query,
                    exact: $queries['filter']['exact'],
                    show: $queries['show'],
                    boolean: 'or'
                );
            });
        }

        $model = static::selectWithRelations(
            $fields,
            mergeFields: true,
            queryModel: $model
        );

        if (isset($queries['sort'])) {
            static::sort($queries['sort'], $model);
        }

        $modelCount = clone $model;

        if (isset($queries['group_by'])) {
            $groupBy = $queries['group_by'];

            if (! static::tableHasColumn($model->getModel()->getTable(), $groupBy)) {
                $groupBy = static::getColumnNameByAlias($model, $groupBy);
            }

            if (static::tableHasColumn($model->getModel()->getTable(), $groupBy)) {
                $modelCount = $modelCount->distinct($groupBy);
                $model = $model->groupBy($groupBy);
            }
        }

        if (isset($queries['pagination']) && $queries['pagination'] == 'cursor') {
            $total = $modelCount->count();

            return DataPaginator::cursorPaginate(
                $model,
                $total,
                $queries['size'] < 0 ? $total : $queries['size'],
                $queries['cursor']
            );
        }

        return DataPaginator::paginate(
            $model,
            $queries['size'] < 0 ? $modelCount->count() : $queries['size'],
            $queries['page']
        );
    }

    /**
     * Select columns and relations from the model based on the given fields.
     *
     * @param  string[]  $fields
     * @param  \Illuminate\Database\Eloquent\Builder|null  $queryModel
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @static
     */
    public static function selectWithRelations(
        array $fields = [],
        bool $mergeFields = false,
        $queryModel = null,
    ) {
        static::loadSchema();
        /** @var \Illuminate\Database\Eloquent\Builder|static */
        $model = $queryModel ?? static::query();

        if ($mergeFields) {
            $fields = static::mergeFields($fields);
        }

        if (count($fields) > 0) {
            static::resolveFieldsAndRelations($model, $fields);
        }

        return $model;
    }
}
