<?php

namespace App\Http\Controllers\LeaveRegistration;

use App\Http\Controllers\Controller;
use App\Jobs\SendAbsenceTransactionsJob;
use App\Models\LeaveRegistration;
use App\Services\LeaveRegistrationService;
use DB;

class LeaveRegistrationActionController extends Controller
{
    /**
     * Stop leave registration.
     */
    public function stop(LeaveRegistration $leaveRegistration)
    {
        if ($leaveRegistration->is_stopped) {
            return back()->with('error', __('leave registration already stopped'));
        }

        $endAt = now();
        $details = LeaveRegistrationService::generateDetailsFromDates(
            $leaveRegistration->start_at,
            $endAt,
            $leaveRegistration->details->max('start_at'),
            true,
        );

        DB::transaction(
            function () use (
                $leaveRegistration,
                $endAt,
                $details,
            ) {
                $leaveRegistration->update([
                    'is_stopped' => true,
                    'end_at' => $endAt,
                ]);
                $leaveRegistration->details()->createMany($details);
            }
        );

        $leaveRegistration->refresh(); // Refresh the model to get the updated details
        SendAbsenceTransactionsJob::dispatchAfterResponse($leaveRegistration);

        return back()->with('success', __('leave registration stopped successfully'));
    }
}
