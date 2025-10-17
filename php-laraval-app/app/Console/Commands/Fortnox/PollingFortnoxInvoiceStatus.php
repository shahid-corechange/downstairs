<?php

namespace App\Console\Commands\Fortnox;

use App\Enums\CacheEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\OperationFailedException;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Log;

class PollingFortnoxInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortnox:polling-invoice-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polling invoice status from fortnox';

    /**
     * Execute the console command.
     */
    public function handle(FortnoxCustomerService $fortnoxService)
    {
        /** @var \Illuminate\Support\Collection<array-key,\App\Models\Invoice> */
        $invoices = Invoice::where('fortnox_invoice_id', '>', 0)
            ->whereNotNull('fortnox_invoice_id')
            ->whereIn('status', [InvoiceStatusEnum::Created(), InvoiceStatusEnum::Sent()])->get();

        while ($invoices->isNotEmpty()) {
            $invoice = $invoices->shift();

            try {
                $fortnoxInvoice = $fortnoxService->getInvoice($invoice->fortnox_invoice_id);

                if ($fortnoxInvoice->final_pay_date) {
                    $paidAt = Carbon::createFromFormat('Y-m-d', $fortnoxInvoice->final_pay_date)->startOfDay();

                    DB::transaction(function () use ($invoice, $paidAt) {
                        $invoice->update(['status' => InvoiceStatusEnum::Paid()]);
                        $invoice->orders()->update([
                            'status' => OrderStatusEnum::Done(),
                            'paid_at' => $paidAt,
                        ]);
                    });
                } elseif ($fortnoxInvoice->booked) {
                    DB::transaction(function () use ($invoice) {
                        $invoice->update(['status' => InvoiceStatusEnum::Sent()]);
                        $invoice->orders()->update([
                            'status' => OrderStatusEnum::Done(),
                        ]);
                    });
                } elseif ($fortnoxInvoice->cancelled) {
                    DB::transaction(function () use ($invoice) {
                        $invoice->update(['status' => InvoiceStatusEnum::Cancel()]);
                        $invoice->orders()->update([
                            'status' => OrderStatusEnum::Cancel(),
                        ]);
                    });
                }
            } catch (OperationFailedException $e) {
                if ($e->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                    $invoices->prepend($invoice);
                    sleep(10);

                    continue;
                }
            }
        }

        // Clear invoices cache so the summations widget will be updated
        Cache::forget(CacheEnum::Invoices());

        $now = now()->utc();
        $info = "Polling invoice status from fortnox at {$now}.";

        Log::channel('fortnox')->info($info);
    }
}
