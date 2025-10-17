<?php

namespace Database\Seeders;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Models\Credit;
use App\Models\CreditCreditTransaction;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            $admin = User::role('Superadmin')->first();
            $users = User::role('Customer')->get();

            foreach ($users as $user) {
                $this->createCredit($user, $admin);
            }
        }
    }

    private function createCredit(User $user, User $admin): void
    {
        $credit = Credit::factory()->forUser($user->id)
            ->setIssuer($admin->id)->create();
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
    }
}
