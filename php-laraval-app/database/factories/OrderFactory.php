<?php

namespace Database\Factories;

use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\Order\OrderPaidByEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Models\Invoice;
use App\Models\ScheduleCleaning;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'status' => OrderStatusEnum::Draft(),
            'paid_by' => OrderPaidByEnum::Invoice(),
        ];
    }

    /**
     * Set the belongs to relationships state.
     */
    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            /** @var \App\Models\Customer */
            $customer = $user->customers->random();
            /** @var \App\Models\Subscription */
            $subscription = $user->subscriptions->random();
            $schedule = ScheduleCleaning::factory()->forSubscription($subscription)->create();
            $invoice = Invoice::findOrCreate(
                $user->id,
                $customer->id,
                now()->month,
                now()->year,
                InvoiceTypeEnum::Cleaning()
            );

            return [
                'user_id' => $user->id,
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'orderable_type' => ScheduleCleaning::class,
                'orderable_id' => $schedule->id,
            ];
        });
    }
}
