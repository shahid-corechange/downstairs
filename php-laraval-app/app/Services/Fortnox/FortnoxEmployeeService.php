<?php

namespace App\Services\Fortnox;

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Exceptions\OperationFailedException;
use App\Jobs\SendAbsenceTransactionJob;
use App\Jobs\SentWorkingHoursJob;
use App\Models\Employee;
use App\Models\LeaveRegistrationDetail;
use App\Models\ScheduleEmployee;
use App\Models\WorkHour;
use Log;

class FortnoxEmployeeService extends FortnoxService
{
    /**
     * OAuth App Name for Fortnox.
     */
    protected string $appName = 'fortnox-employee';

    public function __construct()
    {
        parent::__construct();
        $this->scope = config('services.fortnox.employee_scope');
        $this->token = $this->getToken();
    }

    /**
     * Sync all data without Fortnox ID to Fortnox.
     */
    public function syncAll(): void
    {
        /** @var \Illuminate\Support\Collection<array-key,Employee> */
        $employees = Employee::whereNull('fortnox_id')->get();

        /** @var \Illuminate\Support\Collection<array-key,ScheduleEmployee> */
        $scheduleEmployees = ScheduleEmployee::whereNull('work_hour_id')
            ->where('status', ScheduleEmployeeStatusEnum::Done())
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->get();

        $leaveRegistrationDetails = LeaveRegistrationDetail::whereNull('fortnox_absence_transaction_id')->get();

        $workHours = WorkHour::whereNull('fortnox_attendance_id')->get();

        foreach ($employees as $employee) {
            try {
                $this->syncEmployee($employee);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }

        foreach ($scheduleEmployees as $scheduleEmployee) {
            try {
                SentWorkingHoursJob::dispatchSync($scheduleEmployee);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }

        foreach ($leaveRegistrationDetails as $detail) {
            try {
                SendAbsenceTransactionJob::dispatchSync($detail);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }

        foreach ($workHours as $workHour) {
            try {
                $this->syncWorkHour($workHour);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
