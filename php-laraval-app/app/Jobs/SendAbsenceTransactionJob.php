<?php

namespace App\Jobs;

use App\DTOs\Fortnox\AbsenceTransaction\CreateAbsenceTransactionRequestDTO;
use App\Models\LeaveRegistrationDetail;
use App\Services\Fortnox\FortnoxEmployeeService;

class SendAbsenceTransactionJob extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected LeaveRegistrationDetail $detail,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxEmployeeService $fortnoxService,
    ): void {
        $this->handleWrapper(function () use ($fortnoxService) {
            $hours = ceil($this->detail->start_at->diffInHours($this->detail->end_at));

            $response = $fortnoxService->createAbsenceTransaction(CreateAbsenceTransactionRequestDTO::from([
                'cause_code' => $this->detail->leaveRegistration->type,
                'date' => $this->detail->start_at->format('Y-m-d'),
                'employee_id' => $this->detail->leaveRegistration->employee->fortnox_id,
                'hours' => $hours,
            ]));

            if ($response) {
                $this->detail->update([
                    'fortnox_absence_transaction_id' => $response->id,
                ]);
            }
        });
    }
}
