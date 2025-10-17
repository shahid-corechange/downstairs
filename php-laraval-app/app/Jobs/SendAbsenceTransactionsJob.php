<?php

namespace App\Jobs;

use App\DTOs\Fortnox\AbsenceTransaction\CreateAbsenceTransactionRequestDTO;
use App\Models\LeaveRegistration;
use App\Services\Fortnox\FortnoxEmployeeService;

class SendAbsenceTransactionsJob extends BaseJob
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
        protected LeaveRegistration $leaveRegistration,
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
            foreach ($this->leaveRegistration->details as $detail) {
                if ($detail->fortnox_absence_transaction_id) {
                    // Skip if already sent to Fortnox
                    continue;
                }

                $response = $fortnoxService->createAbsenceTransaction(CreateAbsenceTransactionRequestDTO::from([
                    'cause_code' => $this->leaveRegistration->type,
                    'date' => $detail->start_at->format('Y-m-d'),
                    'employee_id' => $this->leaveRegistration->employee->fortnox_id,
                    'hours' => $detail->hours,
                ]));

                if ($response) {
                    $detail->update([
                        'fortnox_absence_transaction_id' => $response->id,
                    ]);
                }
            }
        });
    }
}
