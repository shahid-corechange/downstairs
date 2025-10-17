<?php

namespace App\Http\Controllers\LeaveRegistration;

use App\DTOs\Employee\EmployeeResponseDTO;
use App\DTOs\LeaveRegistration\CreateLeaveRegistrationRequestDTO;
use App\DTOs\LeaveRegistration\LeaveRegistrationResponseDTO;
use App\DTOs\LeaveRegistration\UpdateLeaveRegistrationRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SendAbsenceTransactionsJob;
use App\Models\Employee;
use App\Models\LeaveRegistration;
use App\Services\LeaveRegistrationService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRegistrationController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'employee',
        'details',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'employee.userId',
        'employee.name',
        'employeeId',
        'type',
        'startAt',
        'endAt',
        'isStopped',
        'rescheduleNeeded',
        'deletedAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            defaultFilter: [
                'isStopped_eq' => false,
            ],
            pagination: 'page',
            sort: ['start_at' => 'asc'],
        );
        $paginatedData = LeaveRegistration::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('LeaveRegistration/Overview/index', [
            'leaveRegistrations' => LeaveRegistrationResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'employees' => $this->getEmployees(),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    private function getEmployees()
    {
        $onlys = [
            'id',
            'name',
        ];

        $employee = Employee::selectWithRelations($onlys)
            ->get();

        return EmployeeResponseDTO::collection($employee)->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            pagination: 'page',
            show: 'all',
        );
        $paginatedData = LeaveRegistration::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            LeaveRegistrationResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLeaveRegistrationRequestDTO $request)
    {
        $data = $request->toArray();
        $endAt = $data['end_at'] ? $request->end_at : null;
        $details = LeaveRegistrationService::generateDetailsFromDates(
            $request->start_at,
            $data['end_at'] ? $request->end_at : null,
        );
        $isStopped = empty($details) ? false : LeaveRegistrationService::shouldStop($endAt, end($details)['start_at']);

        $leaveRegistration = DB::transaction(function () use ($data, $details, $isStopped) {
            $leaveRegistration = LeaveRegistration::create([
                ...$data,
                'is_stopped' => $isStopped,
            ]);
            $leaveRegistration->details()->createMany($details);

            return $leaveRegistration;
        });

        if (! empty($details)) {
            SendAbsenceTransactionsJob::dispatchAfterResponse($leaveRegistration);
        }

        return back()->with('success', __('leave registration created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateLeaveRegistrationRequestDTO $request,
        LeaveRegistration $leaveRegistration
    ) {
        /** @var Carbon */
        $startAt = $request->isNotOptional('start_at') ? $request->start_at : $leaveRegistration->start_at;
        /** @var ?Carbon */
        $endAt = $request->isNotOptional('end_at') ? $request->end_at : $leaveRegistration->end_at;

        $details = LeaveRegistrationService::generateDetailsFromDates($startAt, $endAt);
        $newDetails = array_filter($details, function ($detail) use ($leaveRegistration) {
            return ! $leaveRegistration->details->contains('start_at', $detail['start_at']);
        });

        DB::transaction(function () use ($request, $leaveRegistration, $details, $newDetails) {
            $leaveRegistration->details()
                ->whereNotIn('start_at', array_column($details, 'start_at'))
                ->forceDelete();

            $leaveRegistration->update($request->toArray());
            $leaveRegistration->details()->createMany($newDetails);
        });

        $leaveRegistration->refresh(); // Refresh the model to get the updated details
        SendAbsenceTransactionsJob::dispatchAfterResponse($leaveRegistration);

        return back()->with('success', __('leave registration updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRegistration $leaveRegistration)
    {
        $leaveRegistration->delete();

        return back()->with('success', __('leave registration deleted successfully'));
    }
}
