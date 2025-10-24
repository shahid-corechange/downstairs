<?php

namespace App\Services;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Schedule\ScheduleItemPaymentMethodEnum;
use App\Exceptions\ErrorResponseException;
use App\Models\Credit;
use App\Models\CreditTransaction;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class CreditService
{
    /**
     * Loaded user credits.
     *
     * @var \Illuminate\Database\Eloquent\Collection<array-key,\App\Models\Credit>
     */
    protected $credits;

    /**
     * Indicates if the credits are loaded.
     */
    protected bool $loaded;

    public function __construct()
    {
        $this->credits = collect();
        $this->loaded = false;
    }

    /**
     * Load the credit for the given user.
     *
     * This will optimize the database queries by loading all the credits and transactions
     * for the given user at once. This will be useful for multiple operations that require
     * the user's credit.
     */
    public function load(int $userId)
    {
        $this->credits = Credit::valid()
            ->where('user_id', $userId)
            ->orderBy('valid_until')
            ->get();
        $this->loaded = true;
    }

    /**
     * Get the total valid credit for the given user or all users.
     *
     * @param  int|null  $userId
     * @return int
     */
    public function getTotal($userId = null)
    {
        if (! $userId && $this->loaded) {
            return $this->credits->sum('remaining_amount');
        }

        $query = Credit::valid();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->sum('remaining_amount');
    }

    /**
     * Get the first credit that is about to expire.
     *
     * @param  int|null  $userId
     * @return \App\Models\Credit|null
     */
    public function getExpiring($userId = null)
    {
        if (! $userId && $this->loaded) {
            return $this->credits->first();
        }

        return Credit::valid()
            ->where('user_id', $userId)
            ->orderBy('valid_until')
            ->first();
    }

    /**
     * Check if the user has enough credit.
     *
     * @param  int  $amount
     * @param  int|null  $userId
     * @return bool
     */
    public function hasEnough($amount, $userId = null)
    {
        return $this->getTotal($userId) >= $amount;
    }

    /**
     * Calculate the total amount of the refund.
     *
     * @param  \App\Models\Schedule  $schedule
     * @param  \App\Models\Product|null  $transport
     * @param  \App\Models\Product|null  $material
     * @return int
     */
    public function calculateRefund($schedule, $transport = null, $material = null)
    {
        $fixedPrice = $schedule->subscription?->fixedPrice;
        $service = $schedule->service;
        $minutePerCredit = get_setting(GlobalSettingEnum::CreditMinutePerCredit(), 15);

        // TODO: Need to implement for schedule laundry that hasfixed price.
        if ($fixedPrice && $fixedPrice->is_per_order) {
            if (! $transport) {
                $transport = get_transport();
            }

            if (! $material) {
                $material = get_material();
            }

            // Comprehensive null checking for all potential null reference issues
            $errors = [];
            if (!$transport) {
                $errors[] = 'Transport product is missing from database';
            }
            if (!$material) {
                $errors[] = 'Material product is missing from database';
            }
            if (!$service) {
                $errors[] = 'Service is missing for schedule';
            }
            if (!$fixedPrice || !isset($fixedPrice->total_price)) {
                $errors[] = 'Fixed price or total_price is missing';
            }
            if (!$service || !isset($service->price)) {
                $errors[] = 'Service price is missing';
            }

            if (!empty($errors)) {
                throw new \Exception("CreditService calculation failed for schedule ID {$schedule->id}: " . implode(', ', $errors));
            }

            $totalQuarters = (int) floor(
                ($fixedPrice->total_price - $transport->price - $material->price) / $service->price
            );
            $amount = (int) floor($totalQuarters * 15 / $minutePerCredit);
        } else {
            // Check if schedule quarters is available
            if (!isset($schedule->quarters)) {
                throw new \Exception("Schedule quarters is missing for schedule ID {$schedule->id}");
            }
            $amount = (int) floor($schedule->quarters * 15 / $minutePerCredit);
        }

        // Avoid negative amount
        return $amount < 1 ? 0 : $amount;
    }

    /**
     * Create a credit transaction for the given user.
     *
     * @param  int  $userId
     * @param  string  $type
     * @param  int  $amount
     * @param  string  $description
     * @param  int|null  $scheduleId
     * @param  int|null  $issuerId
     * @param  string|null  $validUntil
     */
    public function createTransaction(
        $userId,
        $type,
        $amount,
        $description,
        $scheduleId = null,
        $issuerId = null,
        $validUntil = null
    ) {
        if (! $this->loaded) {
            $this->load($userId);
        }

        $isGranting = in_array($type, [
            CreditTransactionTypeEnum::Refund(),
            CreditTransactionTypeEnum::Granted(),
        ]);

        if (! $isGranting && ! $this->hasEnough($amount)) {
            throw new ErrorResponseException(
                __('insufficient credits'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $creditTransaction = CreditTransaction::create([
            'user_id' => $userId,
            'schedule_id' => $scheduleId,
            'issuer_id' => $issuerId,
            'type' => $type,
            'total_amount' => $amount,
            'description' => $description,
        ]);

        if ($isGranting) {
            $this->grant(
                $creditTransaction->id,
                $userId,
                $type,
                $amount,
                $description,
                $scheduleId,
                $issuerId,
                $validUntil,
            );
        } else {
            $this->pay($creditTransaction->id, $userId, $amount);
        }
    }

    /**
     * Create an adjustment transaction for the given user.
     *
     * @param  \App\Models\Credit  $credit
     * @param  int  $amount
     * @param  string  $description
     * @param  string  $validUntil
     * @param  int  $issuerId
     */
    public function adjust($credit, $amount, $description, $validUntil, $issuerId)
    {
        $difference = abs($amount - $credit->remaining_amount);

        $credit->update([
            'remaining_amount' => $amount,
            'valid_until' => Carbon::parse($validUntil),
            'issuer_id' => $issuerId,
            'description' => $description,
        ]);

        if ($difference > 0) {
            $isRemoved = $amount - $credit->remaining_amount < 0;

            $transaction = CreditTransaction::create([
                'user_id' => $credit->user_id,
                'schedule_id' => $credit->schedule_id,
                'issuer_id' => $issuerId,
                'type' => $isRemoved ? CreditTransactionTypeEnum::Removed() :
                    CreditTransactionTypeEnum::Updated(),
                'total_amount' => $difference,
                'description' => $description,
            ]);

            $credit->transactions()->attach($transaction->id, ['amount' => $difference]);
        }
    }

    /**
     * Create a refund transaction for the given user.
     * Return the amount of the refund.
     *
     * @param  Schedule  $schedule
     * @param  Product|null  $transport
     * @param  Product|null  $material
     * @param  int|null  $issuerId
     * @return int
     */
    public function refund($schedule, $transport = null, $material = null, $issuerId = null)
    {
        $service = $schedule->service;
        $amount = $this->calculateRefund($schedule, $transport, $material);

        $this->createTransaction(
            $schedule->subscription->user_id,
            CreditTransactionTypeEnum::Refund(),
            $amount,
            $service->name,
            $schedule->id,
            $issuerId
        );

        return $amount;
    }

    /**
     * Refund the items that are paid with credit.
     *
     * @param  Schedule  $schedule
     * @param  int|null  $issuerId
     */
    public function refundItems($schedule, $issuerId = null): int
    {
        $amount = 0;

        foreach ($schedule->items as $item) {
            if ($item->payment_method === ScheduleItemPaymentMethodEnum::Credit()) {
                $this->createTransaction(
                    $schedule->user_id,
                    CreditTransactionTypeEnum::Refund(),
                    $item->itemable->credit_price,
                    $item->itemable->name,
                    $schedule->id,
                    $issuerId
                );

                $amount += $item->itemable->credit_price;
            }
        }

        return $amount;
    }

    /**
     * Grant the given amount to the user's credit.
     *
     * @param  int  $transactionId
     * @param  int  $userId
     * @param  string  $type
     * @param  int  $amount
     * @param  string  $description
     * @param  int|null  $scheduleId
     * @param  int|null  $issuerId
     * @param  string|null  $validUntil
     * @return void
     */
    private function grant(
        $transactionId,
        $userId,
        $type,
        $amount,
        $description,
        $scheduleId = null,
        $issuerId = null,
        $validUntil = null,
    ) {
        if ($validUntil) {
            $creditValidUntil = Carbon::parse($validUntil)->startOfDay();
        } else {
            $creditExpirationDays = get_setting(GlobalSettingEnum::CreditExpirationDays(), 365);
            $creditValidUntil = now()->startOfDay()->addDays($creditExpirationDays);
        }

        $credit = Credit::create([
            'user_id' => $userId,
            'schedule_id' => $scheduleId,
            'issuer_id' => $issuerId,
            'initial_amount' => $amount,
            'remaining_amount' => $amount,
            'type' => $type,
            'description' => $description,
            'valid_until' => $creditValidUntil,
        ]);
        $credit->transactions()->attach($transactionId, ['amount' => $amount]);

        // Update the credits collection if it's not empty (loaded before)
        if ($this->loaded) {
            $this->credits->push($credit);
        }
    }

    /**
     * Pay the given amount with the user's credit.
     */
    private function pay(int $transactionId, int $userId, int $amount): void
    {
        $paymentLeft = $amount;

        while ($paymentLeft > 0) {
            $credit = $this->getExpiring($userId);

            $creditAmount = $credit->remaining_amount;
            /**
             * In case the payment is greater than the credit amount,
             * payment must use another credit.
             */
            $totalPaid = $creditAmount >= $paymentLeft ? $paymentLeft : $creditAmount;
            $remainingCredit = $creditAmount >= $paymentLeft ? $creditAmount - $paymentLeft : 0;
            $paymentLeft = $creditAmount >= $paymentLeft ? 0 : $paymentLeft - $creditAmount;

            $credit->update(['remaining_amount' => $remainingCredit]);
            $credit->transactions()->attach($transactionId, ['amount' => $totalPaid]);

            // Update the credits collection if it's not empty (loaded before)
            if ($remainingCredit === 0 && $this->loaded) {
                $this->credits->shift();
            }
        }
    }
}
