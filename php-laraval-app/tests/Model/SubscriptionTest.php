<?php

namespace Tests\Model;

use App\Models\CustomTask;
use App\Models\FixedPrice;
use App\Models\Order;
use App\Models\Property;
use App\Models\ScheduleCleaning;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionProduct;
use App\Models\SubscriptionStaffDetails;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /** @test */
    public function subscriptionsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('subscriptions', [
                'id',
                'user_id',
                'customer_id',
                'property_id',
                'service_id',
                'team_id',
                'fixed_price_id',
                'frequency',
                'start_at',
                'end_at',
                'start_time_at',
                'end_time_at',
                'quarters',
                'refill_sequence',
                'is_paused',
                'is_fixed',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    // /** @test */
    // public function subscriptionHasTeam(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertInstanceOf(Team::class, $subscription->team);
    // }

    // /** @test */
    // public function subscriptionHasStaffs(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertIsObject($subscription->staffs);
    //     $this->assertInstanceOf(SubscriptionStaffDetails::class, $subscription->staffs->first());
    // }

    // /** @test */
    // public function subscriptionHasScheduleCleanings(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertIsObject($subscription->scheduleCleanings);
    //     $this->assertInstanceOf(
    //         ScheduleCleaning::class,
    //         $subscription->scheduleCleanings->first()
    //     );
    // }

    // /** @test */
    // public function subscriptionHasUser(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertInstanceOf(User::class, $subscription->user);
    // }

    // /** @test */
    // public function subscriptionHasCustomer(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertInstanceOf(User::class, $subscription->user);
    // }

    // /** @test */
    // public function subscriptionHasProperty(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertInstanceOf(Property::class, $subscription->property);
    // }

    // /** @test */
    // public function subscriptionHasProducts(): void
    // {
    //     $product = SubscriptionProduct::first();
    //     $subscription = $product->subscription;

    //     $this->assertIsObject($subscription->products);
    //     $this->assertInstanceOf(
    //         SubscriptionProduct::class,
    //         $subscription->products->first()
    //     );
    // }

    // /** @test */
    // public function subscriptionHasService(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertInstanceOf(Service::class, $subscription->service);
    // }

    // /** @test */
    // public function subscriptionHasTasks(): void
    // {
    //     $subscription = Service::first();

    //     $this->assertIsObject($subscription->tasks);
    //     $this->assertInstanceOf(CustomTask::class, $subscription->tasks->first());
    // }

    // /** @test */
    // public function subscriptionHasFixedPrice(): void
    // {
    //     $subscription = Subscription::first();

    //     $this->assertInstanceOf(FixedPrice::class, $subscription->fixedPrice);
    // }

    // /** @test */
    // public function subscriptionHasOrders(): void
    // {
    //     $order = Order::first();
    //     $order->update([
    //         'subscription_id' => 1,
    //     ]);
    //     $subscription = $order->subscription;

    //     $this->assertIsObject($subscription->orders);
    //     $this->assertInstanceOf(Order::class, $subscription->orders->first());
    // }

    // /** @test */
    // public function subscriptionHasUpdatedSchedules(): void
    // {
    //     $subscription = Subscription::first();

    //     if ($subscription->updatedSchedules->isNotEmpty()) {
    //         $this->assertInstanceOf(ScheduleCleaning::class, $subscription->updatedSchedules->first());
    //     }
    // }
}
