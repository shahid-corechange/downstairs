<?php

namespace App\Helpers\Database;

/**
 * @template TModel of \App\Models\Model
 */
class DataPaginator
{
    /**
     * The model collection.
     *
     * @var TModel[]
     */
    public $data = [];

    /**
     * The pagination information.
     */
    public array $pagination = [];

    /**
     * Create a new paginated data instance.
     *
     * @param  TModel[]  $data
     */
    public function __construct($data, array $pagination)
    {
        $this->data = $data;
        $this->pagination = $pagination;
    }

    /**
     * Paginate the given eloquent builder using cursor pagination method.
     *
     * @param  Illuminate\Database\Eloquent\Builder|TModel  $model
     */
    public static function cursorPaginate($model, int $total, int $size = null, string $cursor = null): self
    {
        $size = $size ?? config('downstairs.pageSize');
        /** @var \Illuminate\Contracts\Pagination\CursorPaginator */
        $paginator = $model->cursorPaginate($size, cursor: $cursor);
        $data = $paginator->items();
        $totalData = count($data);

        $pagination = [
            'total' => $total,
            'size' => $size < $totalData ? $totalData : $size,
            'current_cursor' => $paginator->cursor() ? $paginator->cursor()->encode() : null,
            'next_cursor' => $paginator->nextCursor() ? $paginator->nextCursor()->encode() : null,
            'next_page_url' => $paginator->nextPageUrl(),
            'previous_cursor' => $paginator->previousCursor() ? $paginator->previousCursor()->encode() : null,
            'previous_page_url' => $paginator->previousPageUrl(),
        ];

        return new self($data, $pagination);
    }

    /**
     * Paginate the given eloquent builder using offset pagination method.
     *
     * @param  Illuminate\Database\Eloquent\Builder|TModel  $model
     */
    public static function paginate($model, int $size = null, int $page = 1): self
    {
        $size = $size ?? config('downstairs.pageSize');
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator */
        $paginator = $model->paginate($size, page: $page);
        $data = $paginator->items();
        $totalData = count($data);

        $pagination = [
            'total' => $paginator->total(),
            'size' => $size < $totalData ? $totalData : $size,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'previous_page_url' => $paginator->previousPageUrl(),
        ];

        return new self($data, $pagination);
    }
}
