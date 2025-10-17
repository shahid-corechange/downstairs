<?php

namespace App\Services\Fortnox;

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\MembershipTypeEnum;
use App\Exceptions\OperationFailedException;
use App\Jobs\CreateTaxReductionJob;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use Log;

class FortnoxCustomerService extends FortnoxService
{
    /**
     * OAuth App Name for Fortnox.
     */
    protected string $appName = 'fortnox-customer';

    public function __construct()
    {
        parent::__construct();
        $this->scope = config('services.fortnox.customer_scope');
        $this->token = $this->getToken();
    }

    /**
     * Sync all data without Fortnox ID to Fortnox.
     */
    public function syncAll(): void
    {

        /** @var \Illuminate\Support\Collection<array-key,Customer> */
        $customers = Customer::whereNull('fortnox_id')
            ->whereNull('customer_ref_id')
            ->get();

        /** @var \Illuminate\Support\Collection<array-key,Service> */
        $services = Service::whereNull('fortnox_article_id')->get();

        /** @var \Illuminate\Support\Collection<array-key,Product> */
        $products = Product::whereNull('fortnox_article_id')->get();

        /**
         * Get all private customer invoices that have been sent to fortnox
         * and have not been given a tax reduction
         */
        $invoices = Invoice::join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select('invoices.*', 'customers.membership_type')
            ->where('invoices.status', InvoiceStatusEnum::Created())
            ->whereNotNull('invoices.fortnox_invoice_id')
            ->whereNull('invoices.fortnox_tax_reduction_id')
            ->where('customers.membership_type', MembershipTypeEnum::Private())
            ->get();

        foreach ($customers as $customer) {
            try {
                $this->syncCustomer($customer);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }

        foreach ($services as $service) {
            try {
                $this->syncService($service);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }

        foreach ($products as $product) {
            try {
                $this->syncProduct($product);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }

        // Sync tax reductions for invoices just in case they were failed
        // to create before
        foreach ($invoices as $invoice) {
            try {
                CreateTaxReductionJob::dispatchSync($invoice, null);
            } catch (OperationFailedException $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
