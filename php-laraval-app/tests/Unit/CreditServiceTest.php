<?php

namespace Tests\Unit;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\Credit;
use App\Models\FixedPrice;
use App\Models\ScheduleCleaning;
use App\Services\CreditService;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    public function testCanGetTotalCredit(): void
    {
        $service = new CreditService();
        Credit::factory()->forUser($this->user->id)->create();

        $service->load($this->user->id);

        $total = Credit::valid()
            ->where('user_id', $this->user->id)
            ->sum('remaining_amount');
        $creditTotal = $service->getTotal();

        $this->assertEquals($total, $creditTotal);
    }

    public function testCanGetExpiringCredit(): void
    {
        $service = new CreditService();
        $credit = Credit::factory()->forUser($this->user->id)->create();
        $credit->update([
            'valid_until' => now()->addDays(1),
            'remaining_amount' => 100,
        ]);
        $expiringCredit = $service->getExpiring($this->user->id);

        $this->assertEquals($credit->id, $expiringCredit->id);
    }

    public function testCannotGetExpiringCredit(): void
    {
        $service = new CreditService();
        $expiringCredit = $service->getExpiring($this->user->id);

        $this->assertNull($expiringCredit);
    }

    public function testUserHasEnoughCredit(): void
    {
        $service = new CreditService();
        Credit::factory(1, [
            'initial_amount' => 5,
            'remaining_amount' => 2,
        ])->forUser($this->user->id)->create();

        $service->load($this->user->id);

        $hasEnough = $service->hasEnough(2);

        $this->assertTrue($hasEnough);
    }

    public function testUserDoesNotHaveEnoughCredit(): void
    {
        $service = new CreditService();
        Credit::factory(1, [
            'initial_amount' => 5,
            'remaining_amount' => 2,
        ])->forUser($this->user->id)->create();

        $service->load($this->user->id);

        $hasEnough = $service->hasEnough(3, $this->user->id);

        $this->assertFalse($hasEnough);
    }

    public function testCanCreatePaymentTransaction(): void
    {
        $service = new CreditService();
        $credit = Credit::factory(state: [
            'initial_amount' => 5,
            'remaining_amount' => 5,
        ])->forUser($this->user->id)->create();

        $service->createTransaction(
            $this->user->id,
            CreditTransactionTypeEnum::Payment(),
            3,
            'Payment for cleaning'
        );

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $this->user->id,
            'type' => CreditTransactionTypeEnum::Payment(),
            'total_amount' => 3,
            'description' => 'Payment for cleaning',
        ]);
        $this->assertDatabaseHas('credit_credit_transaction', [
            'credit_id' => $credit->id,
            'amount' => 3,
        ]);
        $this->assertDatabaseHas('credits', [
            'id' => $credit->id,
            'remaining_amount' => 2,
        ]);
    }

    public function testCanCreateRefundTransactionWithPerOrderFixedPrice(): void
    {
        $service = new CreditService();

        /** @var \App\Models\Subscription */
        $subscription = $this->user->subscriptions()->first();
        $fixedPrice = FixedPrice::create([
            'user_id' => $subscription->user_id,
            'is_per_order' => true,
        ]);
        $fixedPrice->rows()->create([
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => 1000,
            'vat_group' => 25,
            'has_rut' => false,
        ]);
        $subscription->update(['fixed_price_id' => $fixedPrice->id]);

        $schedule = ScheduleCleaning::factory(state: [
            'quarters' => $subscription->quarters,
        ])
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        $transport = get_transport();
        $material = get_material();
        $minutePerCredit = get_setting(GlobalSettingEnum::CreditMinutePerCredit(), 15);

        $totalQuarters = (int) floor(
            ($fixedPrice->total_price - $material->price - $transport->price) /
            $subscription->service->price
        );
        $creditAmount = (int) floor($totalQuarters * 15 / $minutePerCredit);

        $service->refund($schedule, $transport, $material, $this->admin->id);
        $newCredit = Credit::where('user_id', $subscription->user_id)
            ->where('schedule_cleaning_id', $schedule->id)
            ->where('issuer_id', $this->admin->id)
            ->where('type', CreditTransactionTypeEnum::Refund())
            ->latest()
            ->first();

        $this->assertEquals($creditAmount, $newCredit->initial_amount);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $subscription->user_id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $creditAmount,
            'description' => $subscription->service->name,
        ]);
        $this->assertDatabaseHas('credit_credit_transaction', [
            'credit_id' => $newCredit->id,
            'amount' => $creditAmount,
        ]);
    }

    public function testCanCreateRefundTransactionWithMonthlyFixedPrice(): void
    {
        $service = new CreditService();

        /** @var \App\Models\Subscription */
        $subscription = $this->user->subscriptions()->first();
        $fixedPrice = FixedPrice::create([
            'user_id' => $subscription->user_id,
            'is_per_order' => false,
        ]);
        $fixedPrice->rows()->create([
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => 1000,
            'vat_group' => 25,
            'has_rut' => false,
        ]);
        $subscription->update(['fixed_price_id' => $fixedPrice->id]);

        $schedule = ScheduleCleaning::factory(state: [
            'quarters' => $subscription->quarters,
        ])
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        $minutePerCredit = get_setting(GlobalSettingEnum::CreditMinutePerCredit(), 15);
        $creditAmount = (int) floor($schedule->quarters * 15 / $minutePerCredit);

        $service->refund($schedule, null, null, $this->admin->id);
        $newCredit = Credit::where('user_id', $subscription->user_id)
            ->where('schedule_cleaning_id', $schedule->id)
            ->where('issuer_id', $this->admin->id)
            ->where('type', CreditTransactionTypeEnum::Refund())
            ->latest()
            ->first();

        $this->assertEquals($creditAmount, $newCredit->initial_amount);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $subscription->user_id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $creditAmount,
            'description' => $subscription->service->name,
        ]);
        $this->assertDatabaseHas('credit_credit_transaction', [
            'credit_id' => $newCredit->id,
            'amount' => $creditAmount,
        ]);
    }

    public function testCanCreateRefundTransactionWithoutFixedPrice(): void
    {
        $service = new CreditService();

        /** @var \App\Models\Subscription */
        $subscription = $this->user->subscriptions()->first();
        $schedule = ScheduleCleaning::factory(state: [
            'quarters' => $subscription->quarters,
        ])
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        $minutePerCredit = get_setting(GlobalSettingEnum::CreditMinutePerCredit(), 15);
        $creditAmount = (int) floor($schedule->quarters * 15 / $minutePerCredit);

        $service->refund($schedule, null, null, $this->admin->id);
        $newCredit = Credit::where('user_id', $subscription->user_id)
            ->where('schedule_cleaning_id', $schedule->id)
            ->where('issuer_id', $this->admin->id)
            ->where('type', CreditTransactionTypeEnum::Refund())
            ->latest()
            ->first();

        $this->assertEquals($creditAmount, $newCredit->initial_amount);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $subscription->user_id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $creditAmount,
            'description' => $subscription->service->name,
        ]);
        $this->assertDatabaseHas('credit_credit_transaction', [
            'credit_id' => $newCredit->id,
            'amount' => $creditAmount,
        ]);
    }
}
