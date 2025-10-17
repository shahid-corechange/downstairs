<?php

namespace Tests\Model;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Credit\CreditTypeEnum;
use App\Models\Credit;
use App\Models\CreditCreditTransaction;
use App\Models\CreditTransaction;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreditTransactionTest extends TestCase
{
    /** @test */
    public function creditTransactionsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('credit_transactions', [
                'id',
                'user_id',
                'schedule_id',
                'issuer_id',
                'type',
                'total_amount',
                'description',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function creditTransactionHasUser(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setType(CreditTypeEnum::Refund())->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_id' => 1,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Refund for cancelation',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertInstanceOf(User::class, $transaction->user);
    }

    /** @test */
    public function creditTransactionHasSchedule(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setType(CreditTypeEnum::Refund())->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_id' => 1,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Refund for cancelation',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertInstanceOf(Schedule::class, $transaction->schedule);
    }

    /** @test */
    public function creditTransactionHasIssuer(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setIssuer($this->admin->id)->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_id' => null,
            'issuer_id' => $this->admin->id,
            'type' => CreditTransactionTypeEnum::Granted(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Granted credit',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertIsObject($transaction->issuer);
        $this->assertInstanceOf(User::class, $transaction->issuer);
    }

    /** @test */
    public function creditTransactionHasCreditCreditTransactions(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setIssuer($this->admin->id)->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_id' => null,
            'issuer_id' => $this->admin->id,
            'type' => CreditTransactionTypeEnum::Granted(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Granted credit',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertIsObject($transaction->creditCreditTransactions);
        $this->assertInstanceOf(CreditCreditTransaction::class, $transaction->creditCreditTransactions->first());
    }
}
