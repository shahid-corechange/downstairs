<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Customer\UpdateFortnoxCustomerRequestDTO;
use App\Models\Customer;
use App\Models\User;
use App\Services\Fortnox\FortnoxCustomerService;
use Str;

class UpdateFortnoxCustomerJob extends BaseJob
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
        protected User $user,
        protected Customer $customer,
        protected bool $referenceFromPrimary = false,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxCustomerService $fortnoxService,
    ): void {
        $this->handleWrapper(function () use ($fortnoxService) {
            $payload = $this->referenceFromPrimary ? [
                'name' => $this->customer->name,
                'email_invoice' => $this->customer->email,
            ] : [
                'name' => $this->customer->name,
                'organisation_number' => $this->customer->identity_number,
                'address1' => $this->customer->address->address,
                'address2' => $this->customer->address->address_2,
                'city' => $this->customer->address->city->name,
                'currency' => $this->user->info->currency,
                'country_code' => $this->customer->address->city->country->code,
                'email' => $this->customer->email,
                'phone1' => $this->customer->phone1,
                'type' => Str::upper($this->customer->membership_type),
                'zip_code' => $this->customer->address->postal_code,
                'default_delivery_types' => [
                    'invoice' => strtoupper($this->customer->invoice_method),
                ],
                'email_invoice' => $this->customer->email,
            ];

            $response = $fortnoxService->updateCustomer(
                $this->customer->fortnox_id,
                UpdateFortnoxCustomerRequestDTO::from($payload)
            );

            if ($response) {
                $this->customer->update([
                    'fortnox_id' => $response->customer_number,
                ]);
            }
        });
    }
}
