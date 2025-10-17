<?php

namespace App\Http\Controllers\Feedback;

use App\DTOs\Feedback\FeedbackResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
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
        'option',
        'description',
        'createdAt',
        'deletedAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            defaultFilter: [
                'deletedAt_eq' => 'null',
            ],
            pagination: 'page',
            sort: ['created_at' => 'desc'],
            show: 'all',
        );
        $paginatedData = Feedback::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Feedback/Overview/index', [
            'feedbacks' => FeedbackResponseDTO::transformCollection(
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
        $paginatedData = Feedback::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            FeedbackResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return back()->with('success', __('feedback deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Feedback $feedback): RedirectResponse
    {
        $feedback->restore();

        return back()->with('success', __('feedback restored successfully'));
    }
}
