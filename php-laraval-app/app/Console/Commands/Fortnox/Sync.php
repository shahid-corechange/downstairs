<?php

namespace App\Console\Commands\Fortnox;

use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\Fortnox\FortnoxEmployeeService;
use Illuminate\Console\Command;
use Log;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all data without Fortnox ID to Fortnox.';

    /**
     * Execute the console command.
     */
    public function handle(FortnoxCustomerService $customerService, FortnoxEmployeeService $employeeService)
    {
        $now = now()->utc();
        scoped_localize('sv_SE', function () use ($customerService, $employeeService) {
            $customerService->syncAll();
            $employeeService->syncAll();
        });

        $info = "Sync all data without Fortnox ID to Fortnox at {$now}.";

        Log::channel('fortnox')->info($info);
    }
}
