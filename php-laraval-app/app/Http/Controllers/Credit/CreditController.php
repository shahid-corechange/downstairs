<?php

namespace App\Http\Controllers\Credit;

use App\DTOs\Credit\CreateCreditRequestDTO;
use App\DTOs\Credit\UpdateCreditRequestDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Credit;
use App\Services\CreditService;
use DB;
use Illuminate\Http\RedirectResponse;

class CreditController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private CreditService $creditService,
    ) {
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateCreditRequestDTO $request): RedirectResponse
    {
        $isUse = $request->amount < 0;

        DB::transaction(function () use ($request, $isUse) {
            $this->creditService->createTransaction(
                $request->user_id,
                $isUse ? CreditTransactionTypeEnum::Payment() : CreditTransactionTypeEnum::Granted(),
                abs($request->amount),
                $request->description,
                issuerId: request()->user()->id,
                validUntil: $request->isNotOptional('valid_until') ? $request->valid_until : null,
            );
        });

        return back()->with('success', __($isUse ? 'credits used successfully' : 'credits granted successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateCreditRequestDTO $request,
        Credit $credit,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $credit) {
            $this->creditService->adjust(
                $credit,
                $request->amount,
                $request->description,
                $request->valid_until,
                issuerId: request()->user()->id,
            );
        });

        return back()->with('success', __('credits updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Credit $credit): RedirectResponse
    {
        DB::transaction(function () use ($credit) {
            $this->creditService->adjust(
                $credit,
                amount: 0,
                description: 'Removed by Admin',
                validUntil: $credit->valid_until,
                issuerId: request()->user()->id,
            );
        });

        return back()->with('success', __('credits removed successfully'));
    }
}
