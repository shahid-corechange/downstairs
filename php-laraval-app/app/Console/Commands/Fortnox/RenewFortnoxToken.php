<?php

namespace App\Console\Commands\Fortnox;

use App\Models\OauthRemoteToken;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\Fortnox\FortnoxEmployeeService;
use Illuminate\Console\Command;
use Log;

class RenewFortnoxToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:renew-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew fornox token';

    /**
     * Execute the console command.
     */
    public function handle(FortnoxCustomerService $customerService, FortnoxEmployeeService $employeeService)
    {
        try {
            $this->renewCustomer($customerService);
        } catch (\Exception $e) {
            Log::channel('fortnox')->info('Error renew fortnox customer '.$e->getMessage());
        }

        try {
            $this->renewEmployee($employeeService);
        } catch (\Exception $e) {
            Log::channel('fortnox')->info('Error renew fortnox employee '.$e->getMessage());
        }
    }

    private function renewCustomer(FortnoxCustomerService $service)
    {
        $now = now()->utc();
        $service->ping();

        $oauth = OauthRemoteToken::where('app_name', 'fortnox-customer')->first();
        $info = "Renew Fortnox customer token at {$now}".
            " Access expires at {$oauth->access_expires_at}.".
            " Refresh expires at {$oauth->refresh_expires_at}.".
            " Updated at {$oauth->updated_at}.";

        Log::channel('fortnox')->info($info);
    }

    private function renewEmployee(FortnoxEmployeeService $service)
    {
        $now = now()->utc();
        $service->ping();

        $oauth = OauthRemoteToken::where('app_name', 'fortnox-employee')->first();
        $info = "Renew Fortnox employee token at {$now}".
            " Access expires at {$oauth->access_expires_at}.".
            " Refresh expires at {$oauth->refresh_expires_at}.".
            " Updated at {$oauth->updated_at}.";

        Log::channel('fortnox')->info($info);
    }
}
