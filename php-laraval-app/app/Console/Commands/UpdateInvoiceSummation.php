<?php

namespace App\Console\Commands;

use App\Enums\CacheEnum;
use App\Models\Invoice;
use App\Services\InvoiceSummationService;
use Cache;
use Illuminate\Console\Command;

class UpdateInvoiceSummation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:summation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invoices summation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(InvoiceSummationService $invoiceSummationService)
    {
        /** @var \Illuminate\Database\Eloquent\Collection<array-key,Invoice> */
        $invoices = Invoice::with(InvoiceSummationService::REQUIRED_FIELDS)->get();

        while ($invoices->isNotEmpty()) {
            $invoice = $invoices->shift();
            $summation = $invoiceSummationService->getSummation($invoice);
            $invoice->update($summation);
        }

        Cache::forget(CacheEnum::Invoices());
    }
}
