<?php

namespace App\Http\Controllers\LeaveRegistration;

use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\LeaveRegistration;
use App\Models\ScheduleEmployee;
use Illuminate\Http\JsonResponse;

class LeaveRegistrationScheduleController extends Controller
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(LeaveRegistration $leaveRegistration): JsonResponse
    {
        $days = get_setting(GlobalSettingEnum::AbsenceRescheduling(), 7);
        $status = [ScheduleEmployeeStatusEnum::Progress(), ScheduleEmployeeStatusEnum::Pending()];
        // If the leave registration is in the past, then the start date is now.
        $start = $leaveRegistration->start_at->isPast() ? now() : $leaveRegistration->start_at;
        // If the end date is not set, use the start date plus the number of days.
        $end = $leaveRegistration->end_at ?? $start->copy()->addDays($days);

        $queries = $this->getQueries(
            filter: [
                'userId' => $leaveRegistration->employee->user_id,
                'schedule_startAt_between' => "{$start->toISOString()},{$end->toISOString()}",
                'status_in' => implode(',', $status),
            ],
            pagination: 'page',
        );
        $paginatedData = ScheduleEmployee::applyFilterSortAndPaginate(
            $queries,
        );

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformCollection($paginatedData->data),
        );
    }
}
