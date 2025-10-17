<?php

namespace App\Console\Commands\Fortnox;

use App\Enums\CacheEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\OperationFailedException;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\TaxReductionService;
use Cache;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Log;

class SentFortnoxInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:sent-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sent fornox invoice';

    /**
     * Execute the console command.
     */
    public function handle(
        FortnoxCustomerService $fortnoxService,
        TaxReductionService $taxReductionService,
    ) {
        $success = 0;
        $now = now()->utc();
        $swedishTime = $now->copy()->setTimezone('Europe/Stockholm');

        /**
         * @var Collection<array-key, Builder|Invoice> $invoices
         */
        $invoices = Invoice::with('customer')
            ->where('status', InvoiceStatusEnum::Created())
            ->where('sent_at', '<=', $swedishTime)
            ->get();
        $total = $invoices->count();

        while ($invoices->isNotEmpty()) {
            $invoice = $invoices->shift();
            $responseBookeep = null;

            /**
             * Don't need to send because of handle by Preventia in Fortnox
             * Just need set status to bookeep
             */
            try {
                try {
                    $responseBookeep = $fortnoxService->bookkeepInvoice($invoice->fortnox_invoice_id);
                } catch (OperationFailedException $e) {
                    if (! str_contains(
                        $e->getMessage(),
                        'Dokument som sparas får inte vara taggad som bokförd'
                    ) &&
                        ! str_contains(
                            $e->getMessage(),
                            'Faktura som bokförs får inte redan vara bokförd'
                        )
                    ) {
                        throw $e;
                    }
                }

                /**
                 * Get tax reduction from Fortnox and save to invoice table for record.
                 */
                $taxReduction = $taxReductionService->get($fortnoxService, $invoice);
            } catch (\Exception $e) {
                if ($e->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                    $this->error('Too many requests, sleep 60 seconds');

                    sleep(60);
                    $invoices->prepend($invoice);

                    continue;
                }

                Log::channel('fortnox')->info("Error in fortnox ID $invoice->fortnox_invoice_id ".$e->getMessage());

                continue;
            }

            if ($responseBookeep && $responseBookeep->status() === Response::HTTP_OK) {
                DB::transaction(function () use (
                    $invoice,
                    $taxReduction,
                ) {
                    $invoice->update([
                        'status' => InvoiceStatusEnum::Sent(),
                        'fortnox_tax_reduction_id' => $taxReduction['tax_reduction'],
                    ]);
                    $invoice->orders()->update([
                        'status' => OrderStatusEnum::Done(),
                    ]);

                    if ($invoice->user->rutCoApplicants->isNotEmpty()) {
                        $id = $invoice->user->rutCoApplicants->first()->id;

                        $invoice->saveMeta([
                            "tax_reduction_co_applicant_id_{$id}" => $taxReduction['co_applicant_tax_reduction'],
                        ]);
                    }
                });

                $success++;
            }
        }

        $info = "Sent fornox invoice at {$now}. Total: {$total}. Success: {$success}.";

        // Clear invoices cache so the summations widget will be updated
        Cache::forget(CacheEnum::Invoices());

        Log::channel('fortnox')->info($info);
    }
}
