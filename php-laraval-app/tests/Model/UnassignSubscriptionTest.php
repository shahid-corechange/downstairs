<?php

namespace Tests\Model;

use App\Models\Product;
use App\Models\Property;
use App\Models\Service;
use App\Models\UnassignSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UnassignSubscriptionTest extends TestCase
{
    // protected UnassignSubscription $unassignSubscription;

    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     $this->unassignSubscription = UnassignSubscription::factory(1)
    //         ->forUser($this->user)
    //         ->create()
    //         ->first();
    // }

    /** @test */
    // public function unassignSubscriptionsDatabaseHasExpectedColumns(): void
    // {
    //     $this->assertTrue(
    //         Schema::hasColumns('unassign_subscriptions', [
    //             'id',
    //             'user_id',
    //             'customer_id',
    //             'property_id',
    //             'service_id',
    //             'frequency',
    //             'start_at',
    //             'end_at',
    //             'start_time_at',
    //             'quarters',
    //             'refill_sequence',
    //             'is_fixed',
    //             'description',
    //             'fixed_price',
    //             'product_ids',
    //             'created_at',
    //             'updated_at',
    //         ]),
    //     );
    // }

    // /** @test */
    // public function unassignSubscriptionHasUser(): void
    // {
    //     $this->assertInstanceOf(User::class, $this->unassignSubscription->user);
    // }

    // /** @test */
    // public function unassignSubscriptionHasCustomer(): void
    // {
    //     $this->assertInstanceOf(User::class, $this->unassignSubscription->user);
    // }

    // /** @test */
    // public function unassignSubscriptionHasProperty(): void
    // {
    //     $this->assertInstanceOf(Property::class, $this->unassignSubscription->property);
    // }

    // /** @test */
    // public function unassignSubscriptionHasService(): void
    // {
    //     $this->assertInstanceOf(Service::class, $this->unassignSubscription->service);
    // }

    // /** @test */
    // public function unassignSubscriptionHasProducts(): void
    // {
    //     $this->unassignSubscription->product_ids = [1, 2, 3];

    //     $this->assertIsObject($this->unassignSubscription->products);
    //     $this->assertInstanceOf(
    //         Product::class,
    //         $this->unassignSubscription->products->first()
    //     );
    // }
}
