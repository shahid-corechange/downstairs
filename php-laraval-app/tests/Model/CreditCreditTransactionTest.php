<?php

namespace Tests\Model;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Credit\CreditTypeEnum;
use App\Models\Credit;
use App\Models\CreditCreditTransaction;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreditCreditTransactionTest extends TestCase
{
    /** @test */
    public function creditCreditTransactionsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('credit_credit_transaction', [
                'id',
                'credit_id',
                'credit_transaction_id',
                'amount',
            ]),
        );
    }

    /** @test */
    public function creditCreditTransactionHasCredit(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setType(CreditTypeEnum::Refund())->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_cleaning_id' => 1,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Refund for cancelation',
        ]);

        $creditCreditTransaction = CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertInstanceOf(Credit::class, $creditCreditTransaction->credit);
    }

    /** @test */
    public function creditCreditTransactionHasCreditTransaction(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setType(CreditTypeEnum::Refund())->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_cleaning_id' => 1,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Refund for cancelation',
        ]);

        $creditCreditTransaction = CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertInstanceOf(CreditTransaction::class, $creditCreditTransaction->creditTransaction);
    }
}
