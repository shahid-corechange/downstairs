<?php

namespace App\Http\Controllers\Logs;

use App\DTOs\Log\AuthLogResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\AuthenticationLog;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuthLogsController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'user.id',
        'user.fullname',
        'ipAddress',
        'userAgent',
        'loginAt',
        'loginSuccessful',
        'logoutAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            sort: ['login_at' => 'desc'],
            pagination: 'page',
            show: 'all'
        );
        $paginatedData = AuthenticationLog::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Log/Auth/index', [
            'authentications' => AuthLogResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = AuthenticationLog::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            AuthLogResponseDTO::transformCollection($paginatedData->data)
        );
    }
}
