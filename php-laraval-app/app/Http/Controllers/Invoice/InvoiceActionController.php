<?php

namespace App\Http\Controllers\Invoice;

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\Fortnox\FortnoxInvoiceService;
use App\Services\TaxReductionService;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class InvoiceActionController extends Controller
{
    public function __construct(
        private FortnoxCustomerService $fortnoxService,
        private TaxReductionService $taxReductionService,
    ) {
    }

    public function create(Invoice $invoice, FortnoxInvoiceService $invoiceService): RedirectResponse
    {
        if ($invoice->status !== InvoiceStatusEnum::Open()) {
            return back()->with('error', __('invoice already created'));
        }

        DB::transaction(function () use ($invoiceService, $invoice) {
            $swedishTime = now()->setTimezone('Europe/Stockholm');

            $invoice->update([
                'sent_at' => $swedishTime,
                'due_at' => $swedishTime->copy()->addDays($invoice->customer->due_days),
            ]);

            scoped_localize('sv_SE', function () use ($invoiceService, $invoice) {
                $invoiceService->create($invoice, $this->fortnoxService);
            });
        });

        return back()->with('success', __('invoice created successfully'));
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== InvoiceStatusEnum::Created()) {
            return back()->with('error', __('invoice not created'));
        }

        $response = $this->fortnoxService->cancelInvoice($invoice->fortnox_invoice_id);

        if ($response) {
            DB::transaction(function () use ($invoice) {
                $invoice->update([
                    'status' => InvoiceStatusEnum::Cancel(),
                ]);

                $invoice->orders()->update([
                    'status' => OrderStatusEnum::Cancel(),
                ]);
            });
        }

        return back()->with('success', __('invoice canceled successfully'));
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === InvoiceStatusEnum::Cancel()) {
            return back()->with('error', __('invoice already canceled'));
        } elseif ($invoice->status !== InvoiceStatusEnum::Created()) {
            return back()->with('error', __('invoice not created'));
        }

        /**
         * Don't need to send because of handle by Preventia in Fortnox
         * Just need set status to bookeep
         */
        $response = $this->fortnoxService->bookkeepInvoice($invoice->fortnox_invoice_id);
        // $this->fortnoxService->sendInvoiceAsEmail($invoice->fortnox_invoice_id);
        if ($response->status() !== Response::HTTP_OK) {
            return back()->with('error', __('invoice sent failed'));
        }

        /**
         * Get tax reduction from Fortnox and save to invoice table for record.
         */
        $taxReduction = $this->taxReductionService->get($this->fortnoxService, $invoice);

        DB::transaction(function () use ($invoice, $taxReduction) {
            $invoice->update([
                'status' => InvoiceStatusEnum::Sent(),
                'fortnox_tax_reduction_id' => $taxReduction['tax_reduction'],
            ]);
            $invoice->orders()->update([
                'status' => OrderStatusEnum::Done(),
            ]);

            if ($invoice->user->rutCoApplicants->isNotEmpty()) {
                $coApplicantId = $invoice->user->rutCoApplicants->first()->id;
                $invoice->saveMeta([
                    "tax_reduction_co_applicant_id_{$coApplicantId}" => $taxReduction['co_applicant_tax_reduction'],
                ]);
            }
        });

        return back()->with('success', __('invoice sent successfully'));
    }
}
