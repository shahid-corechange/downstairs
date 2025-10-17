<?php

namespace App\Console\Commands\Fortnox;

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\Fortnox\FortnoxInvoiceService;
use DB;
use Illuminate\Console\Command;
use Log;

class CreateFortnoxInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:create-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create fornox invoice';

    /**
     * Execute the console command.
     */
    public function handle(FortnoxCustomerService $fortnoxService, FortnoxInvoiceService $invoiceService)
    {
        $now = now()->utc();
        $swedishTime = $now->copy()->setTimezone('Europe/Stockholm');

        $invoices = Invoice::where('status', InvoiceStatusEnum::Open())
            ->where('sent_at', '<=', $swedishTime)
            ->get();

        app()->setLocale('sv_SE');

        foreach ($invoices as $invoice) {
            $this->info("Create fornox invoice for invoice ID: {$invoice->id}");

            DB::transaction(
                function () use ($invoice, $invoiceService, $fortnoxService, $swedishTime) {
                    $invoice->update([
                        'sent_at' => $swedishTime,
                        'due_at' => $swedishTime->copy()->addDays($invoice->customer->due_days),
                    ]);

                    try {
                        $invoiceService->create($invoice, $fortnoxService);
                    } catch (\Exception $e) {
                        $msg = "Create fornox invoice failed. Invoice ID: {$invoice->id}. Error: {$e->getMessage()}";
                        $this->error($msg);
                        Log::channel('fortnox')->error($msg);
                    }
                }
            );
        }

        $invoiceIds = $invoices->pluck('id')->toArray();
        $success = Invoice::whereIn('id', $invoiceIds)
            ->where('status', InvoiceStatusEnum::Created())->count();
        $info = "Create fornox invoice at {$now}. Total: {$invoices->count()}. Success: {$success}.";

        Log::channel('fortnox')->info($info);
    }
}
