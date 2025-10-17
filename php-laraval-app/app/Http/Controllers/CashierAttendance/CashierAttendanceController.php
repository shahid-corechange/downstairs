<?php

namespace App\Http\Controllers\CashierAttendance;

use App\DTOs\CashierAttendance\CashierAttendanceRequestDTO;
use App\DTOs\CashierAttendance\CashierAttendanceResponseDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CashierAttendanceToWorkHourJob;
use App\Models\CashierAttendance;
use DB;
use Illuminate\Http\JsonResponse;

class CashierAttendanceController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            defaultFilter: [
                'store_id_eq' => $storeId,
            ],
        );
        $paginatedData = CashierAttendance::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CashierAttendanceResponseDTO::transformCollection($paginatedData->data)
        );
    }

    public function checkIn(CashierAttendanceRequestDTO $request)
    {
        $storeId = request()->session()->get('store_id');
        $attendance = CashierAttendance::where('user_id', $request->user_id)
            ->where('store_id', $storeId)
            ->where('check_out_at', null)
            ->first();

        if ($attendance) {
            return back()->with('error', __('cannot check in more than once'));
        }

        CashierAttendance::create([
            'user_id' => $request->user_id,
            'store_id' => $storeId,
            'check_in_at' => now(),
            'check_in_causer_id' => auth()->user()->id,
        ]);

        return back()->with('success', __('check in successfully'));
    }

    public function checkOut(CashierAttendanceRequestDTO $request)
    {
        $attendance = CashierAttendance::where('user_id', $request->user_id)
            ->where('store_id', request()->session()->get('store_id'))
            ->where('check_out_at', null)
            ->first();

        if (! $attendance) {
            return back()->with('error', __('cannot check out'));
        }

        $attendances = DB::transaction(function () use ($attendance) {
            $startAt = $attendance->check_in_at->copy()->setTimezone('Europe/Stockholm');
            $endAt = now()->copy()->setTimezone('Europe/Stockholm');

            // If check in and check out is not same day,
            // we need to create a new attendance for the next day.
            if (! $startAt->isSameDay($endAt)) {
                $attendance->update([
                    'check_out_at' => $startAt->copy()->endOfDay()->utc(),
                    'check_out_causer_id' => auth()->user()->id,
                ]);

                $attendance2 = CashierAttendance::create([
                    'user_id' => $attendance->user_id,
                    'store_id' => $attendance->store_id,
                    'check_in_at' => $endAt->copy()->startOfDay()->utc(),
                    'check_in_causer_id' => auth()->user()->id,
                    'check_out_at' => $endAt->utc(),
                    'check_out_causer_id' => auth()->user()->id,
                ]);

                $attendance2->update([
                    'check_out_at' => $endAt->utc(),
                    'check_out_causer_id' => auth()->user()->id,
                ]);

                return [$attendance, $attendance2];
            } else {
                $attendance->update([
                    'check_out_at' => $endAt->utc(),
                    'check_out_causer_id' => auth()->user()->id,
                ]);

                return [$attendance];
            }
        });

        foreach ($attendances as $attendance) {
            CashierAttendanceToWorkHourJob::dispatchAfterResponse($attendance);
        }

        return back()->with('success', __('check out successfully'));
    }
}
