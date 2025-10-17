<?php

namespace App\Http\Traits;

use App\Enums\Api\QueryFilterEnum;
use App\Enums\Api\SortEnum;
use App\Exceptions\InvalidQueryFilterException;
use Str;

trait QueryStringTrait
{
    /**
     * Get all query string parameters, except page and size.
     * Transform any valid filter or sort query into array.
     */
    public function getAllQueries(): array
    {
        $query = request()->query();
        $sort = $this->getSortQuery(
            $query,
            fn ($table) => $table,
            fn ($column) => $column
        );
        $filters = $this->getFilterQuery(
            $query,
            fn ($table) => $table,
            fn ($column) => $column,
            true,
            true
        );
        $orFilter = $this->getOrFilterQuery(
            isset($query['or']) ? $query['or'] : [],
            fn ($table) => $table,
            fn ($column) => $column,
            true,
            true
        );
        $exactFilter = isset($query['filter']) && $query['filter'] === 'exact';
        unset(
            $query['filter'],
            $query['or'],
            $query['sort'],
            $query['page'],
            $query['size'],
            $query['cursor'],
            $query['pagination'],
            $query['groupBy']
        );

        foreach ($filters as $filter) {
            $key = str_replace('.', '_', $filter['key']);
            unset($query[$key.'_'.$filter['criteria']]);
        }

        return [
            'sort' => count($sort) > 0 ? $sort : null,
            'filter' => [
                'filters' => $filters,
                'or_filters' => $orFilter,
                'exact' => $exactFilter,
            ],
            'query' => count($query) > 0 ? $query : null,
        ];
    }

    /**
     * Get all query string parameters
     * and transform them into filter, sort, page and size.
     *
     * @param  array<string,string>  $filter
     * @param  array<string,string>  $sort
     */
    public function getQueries(
        array $filter = [],
        array $defaultFilter = [],
        array $sort = ['id' => 'asc'],
        string $groupBy = null,
        int $size = null,
        int $page = 1,
        string $pagination = 'cursor',
        string $show = 'active',
        bool $exactFilter = false
    ): array {
        $query = request()->query();
        $filter = $this->getFilterQuery(
            array_merge($query, $filter),
            fn ($table) => Str::camel($table),
            fn ($column) => Str::snake($column),
        );
        $defaultFilter = $this->getFilterQuery(
            $defaultFilter,
            fn ($table) => Str::camel($table),
            fn ($column) => Str::snake($column),
        );
        $filter = $this->mergeFilterQuery($filter, $defaultFilter);
        $orFilter = $this->getOrFilterQuery(
            isset($query['or']) ? $query['or'] : [],
            fn ($table) => Str::camel($table),
            fn ($column) => Str::snake($column),
        );
        $exactFilter = isset($query['filter']) ? $query['filter'] === 'exact' : $exactFilter;
        $sortQuery = $this->getSortQuery(
            $query,
            fn ($table) => Str::camel($table),
            fn ($column) => Str::snake($column)
        );
        $sort = array_merge($sortQuery, array_diff_key($sort, $sortQuery));
        $groupBy = Str::snake(isset($query['groupBy']) ? $query['groupBy'] : $groupBy);
        $size = isset($query['size']) ? intval($query['size']) : $size ?? config('downstairs.pageSize');
        $cursor = isset($query['cursor']) ? $query['cursor'] : null;
        $page = isset($query['page']) ? intval($query['page']) : $page;
        $page = $page < 1 ? 1 : $page;
        $pagination = isset($query['pagination']) ? $query['pagination'] : $pagination;
        $pagination = $pagination === 'page' ? 'page' : 'cursor';
        $show = isset($query['show']) ? $query['show'] : $show;
        $show = $show === 'all' ? 'all' :
            ($show === 'deleted' ? 'deleted' : 'active');

        return [
            'filter' => [
                'filters' => $filter,
                'or_filters' => $orFilter,
                'exact' => $exactFilter,
            ],
            'sort' => $sort,
            'group_by' => $groupBy,
            'size' => $size,
            'cursor' => $cursor,
            'page' => $page,
            'pagination' => $pagination,
            'show' => $show,
        ];
    }

    /**
     * Transform query string into sort query. In case of multiple sort,
     * use comma to separate values. Example sort=date.asc,address.id.desc
     *
     * @param  string[]  $query
     * @param  callable(string $table): string  $tableNameTransformer
     * @param  callable(string $column): string  $columnNameTransformer
     */
    private function getSortQuery(
        array $query,
        $tableNameTransformer,
        $columnNameTransformer
    ): array {
        $sortQuery = $query['sort'] ?? '';

        if (! $sortQuery) {
            return [];
        }

        $sortItems = explode(',', $sortQuery);

        $sorts = array_reduce(
            $sortItems,
            function ($accumulator, $value) use ($tableNameTransformer, $columnNameTransformer) {
                $vals = explode('.', $value);
                $keys = array_slice($vals, 0, -1);
                $key = implode('.', array_map(
                    fn ($item) => $item === end($keys) ? $columnNameTransformer($item) : $tableNameTransformer($item),
                    $keys
                ));
                $type = end($vals);

                if (! in_array($type, SortEnum::values())) {
                    $type = 'asc';
                }

                return [...$accumulator, $key => $type];
            },
            []
        );

        return $sorts;
    }

    /**
     * Transform query string into filter query. The properties and criteria
     * are separated by periods. Example:
     * user.properties.id.equal=4
     *
     * @param  string[]  $query
     * @param  callable(string $table): string  $tableNameTransformer
     * @param  callable(string $column): string  $columnNameTransformer
     * @param  bool  $excludeInvalid
     * @param  bool  $joinKey
     */
    private function getFilterQuery(
        array $query,
        $tableNameTransformer,
        $columnNameTransformer,
        $excludeInvalid = false,
        $joinKey = false
    ): array {
        $filters = [];
        $excludeKeys = ['size', 'page', 'sort', 'include', 'exclude', 'except', 'only', 'or', 'pagination'];

        foreach ($query as $key => $value) {
            if (in_array($key, $excludeKeys) || (! $value && $value != '0')) {
                continue;
            }

            $items = explode('_', $key);
            $criteria = end($items);
            $isValidCriteria = in_array($criteria, QueryFilterEnum::values());

            if ($excludeInvalid && ! $isValidCriteria) {
                continue;
            }

            $criteria = $isValidCriteria ? $criteria : QueryFilterEnum::Eq();
            $keys = array_slice($items, 0, $isValidCriteria ? -1 : null);
            $keys = array_map(
                fn ($item) => $item === end($keys) ?
                    $columnNameTransformer($item) : $tableNameTransformer($item),
                $keys
            );
            $transformedValue = $this->transformFilterValue($criteria, $value);

            if ($joinKey) {
                array_push($filters, [
                    'key' => implode('.', $keys),
                    'criteria' => $criteria,
                    'value' => $transformedValue,
                ]);
            } else {
                array_push($filters, [
                    'keys' => $keys,
                    'criteria' => $criteria,
                    'value' => $transformedValue,
                ]);
            }
        }

        return $filters;
    }

    /**
     * Transform `or[]` query string into filter query. The properties and criteria
     * are separated by periods, also each filter is separated by `|`. Example:
     * or[]=user.properties.id.equal=4|user.first_name.like=John
     *
     * @param  string[]  $query
     * @param  callable(string $table): string  $tableNameTransformer
     * @param  callable(string $column): string  $columnNameTransformer
     * @param  bool  $excludeInvalid
     * @param  bool  $joinKey
     */
    private function getOrFilterQuery(
        array $query,
        $tableNameTransformer,
        $columnNameTransformer,
        $excludeInvalid = false,
        $joinKey = false
    ): array {
        $filters = [];

        foreach ($query as $value) {
            $items = explode('|', $value);
            $andQuery = [];

            foreach ($items as $item) {
                $filter = explode('=', $item);
                $key = str_replace('.', '_', $filter[0]);
                $andQuery[$key] = $filter[1];
            }

            $filterItem = $this->getFilterQuery(
                $andQuery,
                $tableNameTransformer,
                $columnNameTransformer,
                $excludeInvalid,
                $joinKey
            );
            $filters[] = $filterItem;
        }

        return $filters;
    }

    /**
     * Transform value of the filter according to the criteria.
     *
     * @return string|string[]
     */
    private function transformFilterValue(string $criteria, string $value): string|array
    {
        $commaSeparatedPattern = '/^[^,]+(?:,[^,]+)*(?!,)$/';
        $betweenPattern = '/^[^,]+,[^,]+$/';

        if ($criteria == QueryFilterEnum::In() && ! preg_match($commaSeparatedPattern, $value)) {
            throw new InvalidQueryFilterException(__('error query filter in'));
        } elseif ($criteria == QueryFilterEnum::NotIn() && ! preg_match($commaSeparatedPattern, $value)) {
            throw new InvalidQueryFilterException(__('error query filter notin'));
        } elseif ($criteria == QueryFilterEnum::Between() && ! preg_match($betweenPattern, $value)) {
            throw new InvalidQueryFilterException(__('error query filter between'));
        }

        if (! in_array($criteria, [QueryFilterEnum::In(), QueryFilterEnum::NotIn(), QueryFilterEnum::Between()])) {
            return $value;
        }

        return explode(',', str_replace(', ', ',', $value));
    }

    /**
     * Merge filter query with the given default filters.
     */
    private function mergeFilterQuery(array $filters, array $defaultFilters): array
    {
        $defaultFilters = array_filter($defaultFilters, function ($item) use ($filters) {
            foreach ($filters as $filter) {
                if ($filter['keys'] === $item['keys']) {
                    return false;
                }
            }

            return true;
        });

        return array_merge($defaultFilters, $filters);
    }
}
