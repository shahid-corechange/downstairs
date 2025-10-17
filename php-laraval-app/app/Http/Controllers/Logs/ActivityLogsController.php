<?php

namespace App\Http\Controllers\Logs;

use App\DTOs\Log\ActivityLogResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogsController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user.roles',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'user.id',
        'user.fullname',
        'user.roles.id',
        'user.roles.name',
        'subjectId',
        'subjectType',
        'event',
        'createdAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            ['causerType_eq' => User::class],
            pagination: 'page',
            show: 'all',
            sort: ['created_at' => 'desc']
        );
        $paginatedData = Activity::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Log/Activity/index', [
            'activities' => ActivityLogResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            filter: ['causerType_eq' => User::class],
        );
        $paginatedData = Activity::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ActivityLogResponseDTO::transformCollection($paginatedData->data)
        );
    }
}
