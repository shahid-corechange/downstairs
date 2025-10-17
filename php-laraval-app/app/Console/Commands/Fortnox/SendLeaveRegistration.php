<?php

namespace App\Console\Commands\Fortnox;

use App\Jobs\SendAbsenceTransactionsJob;
use App\Models\LeaveRegistration;
use App\Services\LeaveRegistrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;

class SendLeaveRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:send-leave-registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send leave registration data that already in the past'.
        ' to Fortnox absence transaction.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now()->utc();
        $endOfLastMonth = now()->subMonth()->endOfMonth();

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,LeaveRegistration> */
        $leaveRegistrations = LeaveRegistration::with('details')
            ->where('is_stopped', false)
            ->where('start_at', '<=', $endOfLastMonth)
            ->get();

        $counter = 0;

        foreach ($leaveRegistrations as $leaveRegistration) {
            $details = LeaveRegistrationService::generateDetails($leaveRegistration);

            if (empty($details)) {
                // Skip if no details generated for leave registration
                continue;
            }

            $isStopped = LeaveRegistrationService::shouldStop($leaveRegistration->end_at, end($details)['start_at']);

            DB::transaction(function () use ($leaveRegistration, $details, $isStopped) {
                $leaveRegistration->details()->createMany($details);
                $leaveRegistration->update([
                    'is_stopped' => $isStopped,
                ]);
            });

            // Because we eager load the details, we need to refresh the model
            // to get the newly created details
            $leaveRegistration->refresh();

            SendAbsenceTransactionsJob::dispatchSync($leaveRegistration);
            $counter++;
        }

        $info = "Send fornox absence transaction at {$now}.".
            " Total leave registration: {$counter}";

        Log::channel('fortnox')->info($info);
    }
}
