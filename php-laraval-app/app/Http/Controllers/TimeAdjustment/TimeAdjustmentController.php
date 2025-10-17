<?php

namespace App\Http\Controllers\TimeAdjustment;

use App\DTOs\TimeAdjustment\CreateTimeAdjustmentRequestDTO;
use App\DTOs\TimeAdjustment\TimeAdjustmentResponseDTO;
use App\DTOs\TimeAdjustment\UpdateTimeAdjustmentRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\UpdateWorkHourJob;
use App\Models\TimeAdjustment;
use Illuminate\Http\JsonResponse;

class TimeAdjustmentController extends Controller
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = TimeAdjustment::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            TimeAdjustmentResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Create a new time adjustment.
     */
    public function store(CreateTimeAdjustmentRequestDTO $request)
    {
        $timeAdjustment = TimeAdjustment::create([
            ...$request->toArray(),
            'causer_id' => auth()->id(),
        ]);

        UpdateWorkHourJob::dispatchAfterResponse($timeAdjustment->schedule->workHour);

        return back()->with('success', __('time adjustment created successfully'));
    }

    /**
     * Update the specified time adjustment.
     */
    public function update(
        TimeAdjustment $timeAdjustment,
        UpdateTimeAdjustmentRequestDTO $request
    ) {
        if ($timeAdjustment->schedule->work_quarters + $request->quarters < 0) {
            return back()
                ->with('error', __('total quarters employee worked on cannot be less than 0'));
        }

        $timeAdjustment->update([
            ...$request->toArray(),
            'causer_id' => auth()->id(),
        ]);

        UpdateWorkHourJob::dispatchAfterResponse($timeAdjustment->schedule->workHour);

        return back()->with('success', __('time adjustment updated successfully'));
    }

    /**
     * Remove the specified time adjustment.
     */
    public function destroy(TimeAdjustment $timeAdjustment)
    {
        $timeAdjustment->delete();

        UpdateWorkHourJob::dispatchAfterResponse($timeAdjustment->schedule->workHour);

        return back()->with('success', __('time adjustment deleted successfully'));
    }
}
