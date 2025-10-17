<?php

namespace Tests\Model;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Models\Credit;
use App\Models\CreditCreditTransaction;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreditTest extends TestCase
{
    /** @test */
    public function creditsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('credits', [
                'id',
                'user_id',
                'schedule_cleaning_id',
                'issuer_id',
                'initial_amount',
                'remaining_amount',
                'type',
                'description',
                'valid_until',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function creditHasUser(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)->create();

        $this->assertInstanceOf(User::class, $credit->user);
    }

    /** @test */
    public function creditHasTransactions(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_cleaning_id' => null,
            'type' => CreditTransactionTypeEnum::Granted(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Granted credit',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertIsObject($credit->transactions);
        $this->assertInstanceOf(CreditTransaction::class, $credit->transactions->first());
    }

    /** @test */
    public function creditHasIssuer(): void
    {
        $credit = Credit::factory()->forUser($this->user->id)
            ->setIssuer($this->admin->id)->create();
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_cleaning_id' => null,
            'type' => CreditTransactionTypeEnum::Granted(),
            'total_amount' => $credit->initial_amount,
            'description' => 'Granted credit',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => $credit->initial_amount,
        ]);

        $this->assertIsObject($credit->issuer);
        $this->assertInstanceOf(User::class, $credit->issuer);
    }
}
