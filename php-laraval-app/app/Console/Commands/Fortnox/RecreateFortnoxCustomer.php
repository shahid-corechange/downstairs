<?php

namespace App\Console\Commands\Fortnox;

use App\DTOs\Fortnox\Customer\CreateFortnoxCustomerRequestDTO;
use App\DTOs\Fortnox\Customer\UpdateFortnoxCustomerRequestDTO;
use App\Exceptions\OperationFailedException;
use App\Models\Customer;
use App\Services\Fortnox\FortnoxCustomerService;
use Illuminate\Console\Command;
use Str;

class RecreateFortnoxCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:recreate-customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inactivate all existing customers and recreate them';

    /**
     * Execute the console command.
     */
    public function handle(FortnoxCustomerService $fortnoxService)
    {
        // Inactivate all existing customers
        do {
            $existingCustomers = $fortnoxService->getCustomers(filter: 'active');

            $this->info('Found '.$existingCustomers->count().' active customers');

            foreach ($existingCustomers as $customer) {
                $fortnoxService->updateCustomer(
                    $customer->customer_number,
                    UpdateFortnoxCustomerRequestDTO::from([
                        'name' => $customer->name,
                        'active' => false,
                    ])
                );
            }
        } while ($existingCustomers->count() > 0);

        $customers = Customer::whereNotNull('fortnox_id')->get();
        $min = $customers->min('fortnox_id') ?? 0;
        $max = $customers->max('fortnox_id') ?? 9999;

        if ($min < 10000 || $min > 10200) {
            // Clear all fortnox_id if they are not starting from 10000-10200 (added 200 for safety)
            Customer::whereNotNull('fortnox_id')->update(['fortnox_id' => null]);
            $max = 9999;
        }

        $notSyncedCustomers = Customer::whereNull('fortnox_id')
            ->whereNull('customer_ref_id')
            ->get();
        $customerNumber = $max + 1;

        $this->info('Found '.$notSyncedCustomers->count().' customers to sync');

        while ($notSyncedCustomers->count() > 0) {
            $this->info('Syncing customer '.$customerNumber);
            $customer = $notSyncedCustomers->shift();

            try {
                $response = $fortnoxService->createCustomer(CreateFortnoxCustomerRequestDTO::from([
                    'customer_number' => $customerNumber,
                    'name' => $customer->name,
                    'organisation_number' => $customer->identity_number,
                    'address1' => $customer->address->address,
                    'address2' => $customer->address->address2,
                    'city' => $customer->address->city->name,
                    'currency' => $customer->users->first()->info->currency,
                    'country_code' => $customer->address->city->country->code,
                    'email' => $customer->email,
                    'phone1' => $customer->phone1,
                    'type' => Str::upper($customer->membership_type),
                    'zip_code' => $customer->address->postal_code,
                    'default_delivery_types' => [
                        'invoice' => strtoupper($customer->invoice_method),
                    ],
                    'email_invoice' => $customer->email,
                ]));

                if ($response) {
                    $customer->update([
                        'fortnox_id' => $response->customer_number,
                    ]);

                    // Update all customers that reference this customer
                    Customer::where('customer_ref_id', $customer->id)
                        ->update(['fortnox_id' => $response->customer_number]);
                }

                $customerNumber++;
            } catch (OperationFailedException $e) {
                if (str_contains($e->getMessage(), 'används redan')) {
                    // If customer number is already in use, try next number
                    $this->error('Customer number '.$customerNumber.' is already in use');
                    $customerNumber++;
                } else {
                    // We assumed that error is because of rate limit
                    $this->error('Rate limit reached, waiting for 60 seconds');
                    sleep(60);
                }

                // Add customer back to the queue
                $notSyncedCustomers->prepend($customer);
            }
        }
    }
}
