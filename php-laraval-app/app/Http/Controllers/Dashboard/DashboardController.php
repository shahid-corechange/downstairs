<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\LeaveRegistration;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleEmployee;
use App\Models\Service;
use App\Models\UnassignSubscription;
use App\Services\CreditService;
use Auth;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Health\ResultStores\ResultStore;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;

class DashboardController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Display the dashboard.
     */
    public function index(CreditService $creditService, ResultStore $healthCheckResultStore): Response
    {
        $unsyncEmployees = Employee::whereNull('fortnox_id')->count();
        $unsyncCustomers = Customer::whereNull('fortnox_id')->count();
        $unsyncServices = Service::whereNull('fortnox_article_id')->count();
        $unsyncProducts = Product::whereNull('fortnox_article_id')->count();
        $totalCredit = $creditService->getTotal();
        $unsyncWorkersAttendance = ScheduleEmployee::whereNull('work_hour_id')
            ->where('status', ScheduleEmployeeStatusEnum::Done())
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->count();

        $servicesStatus = $this->getServicesStatus($healthCheckResultStore);

        return Inertia::render('Dashboard/index', [
            'unsyncData' => $unsyncEmployees + $unsyncCustomers + $unsyncServices + $unsyncProducts
                + $unsyncWorkersAttendance,
            'totalCredit' => $totalCredit,
            'servicesStatus' => $servicesStatus,
            'leaveRegistrations' => $this->getLeaveRegistrations(),
            ...$this->getUnassignSubscriptions(),
            ...$this->getCanceledSchedules(),
        ]);
    }

    private function getServicesStatus(ResultStore $healthCheckResultStore)
    {
        $latestCheckResult = $healthCheckResultStore->latestResults();

        if ($latestCheckResult) {
            $lastCheckedAt = Carbon::parse($latestCheckResult->finishedAt);

            return $latestCheckResult->storedCheckResults->map(fn (StoredCheckResult $item) => [
                'name' => $item->label,
                'status' => $item->status,
                'lastCheckedAt' => $lastCheckedAt,
            ])->values();
        }

        return [];
    }

    private function getUnassignSubscriptions()
    {
        $user = Auth::user();
        $baseTime = now()->setTimezone($user->info->timezone);
        $baseDay = $baseTime->copy()->addDay();
        $startOfTomorrow = $baseDay->copy()->startOfDay()->utc()->toDateTimeString();
        $endOfTomorrow = $baseDay->copy()->endOfDay()->utc()->toDateTimeString();

        $plannedToStartTomorrow = $this->getUnassignSubscriptionIds($startOfTomorrow, $endOfTomorrow);

        $baseWeek = $baseTime->copy()->addDays(7);
        $startOfNextWeek = $baseWeek->copy()->startOfWeek()
            ->startOfDay()->utc()->toDateTimeString();
        $endOfNextWeek = $baseWeek->copy()->endOfWeek()
            ->endOfDay()->utc()->toDateTimeString();

        $plannedToStartNextWeek = $this->getUnassignSubscriptionIds($startOfNextWeek, $endOfNextWeek);

        $alreadyPassed = UnassignSubscription::where(
            'start_at',
            '<',
            $baseTime->copy()->format('Y-m-d')
        )->get()->pluck('id')->toArray();

        return [
            'plannedToStartTomorrow' => $plannedToStartTomorrow,
            'plannedToStartNextWeek' => $plannedToStartNextWeek,
            'alreadyPassed' => $alreadyPassed,
        ];
    }

    /**
     * Get the unassign subscription ids
     *
     * @param  string  $start
     * @param  string  $end
     * @return array
     */
    private function getUnassignSubscriptionIds($start, $end)
    {
        return UnassignSubscription::select('id')
            ->where(function ($query) use ($start, $end) {
                // For cleaning subscriptions
                $query->whereRaw("JSON_EXTRACT(cleaning_detail, '$.start_time') IS NOT NULL")
                    ->whereRaw(
                        "CONCAT(start_at, ' ', JSON_UNQUOTE(JSON_EXTRACT(cleaning_detail, '$.start_time')))".
                            ' BETWEEN ? AND ?',
                        [$start, $end]
                    );
            })
            ->orWhere(function ($query) use ($start, $end) {
                // For laundry subscriptions
                $query->whereRaw("JSON_EXTRACT(laundry_detail, '$.pickup_time') IS NOT NULL")
                    ->whereRaw(
                        "CONCAT(start_at, ' ', JSON_UNQUOTE(JSON_EXTRACT(laundry_detail, '$.pickup_time')))".
                            ' BETWEEN ? AND ?',
                        [$start, $end]
                    );
            })
            ->get()
            ->pluck('id')
            ->toArray();
    }

    private function getLeaveRegistrations()
    {

        $onlys = [
            'id',
            'reschedule_needed',
        ];
        $leaveRegistrations = LeaveRegistration::selectWithRelations($onlys)
            ->where('is_stopped', false)
            ->get();

        return $leaveRegistrations->filter(function ($data) {
            return $data->reschedule_needed;
        })
            ->pluck('id')
            ->toArray();
    }

    private function getCanceledSchedules()
    {
        $user = Auth::user();
        $baseTime = now()->setTimezone($user->info->timezone);
        $startAt = $baseTime->copy()->startOfDay()->utc()->toDateTimeString();
        $endAt = $baseTime->copy()->endOfDay()->utc()->toDateTimeString();

        $canceledSchedules = Schedule::canceled()
            ->whereBetween('canceled_at', [$startAt, $endAt])
            ->get();

        $canceledByCustomer = $canceledSchedules
            ->filter(fn ($data) => $data->canceled_type === 'customer')
            ->count();

        $canceledByTeam = $canceledSchedules
            ->filter(fn ($data) => $data->canceled_type === 'employee')
            ->count();

        $canceledByAdmin = $canceledSchedules
            ->filter(fn ($data) => $data->canceled_type === 'admin')
            ->count();

        return [
            'canceledByCustomer' => $canceledByCustomer,
            'canceledByTeam' => $canceledByTeam,
            'canceledByAdmin' => $canceledByAdmin,
        ];
    }
}
