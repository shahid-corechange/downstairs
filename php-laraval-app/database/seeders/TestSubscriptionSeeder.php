<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionProduct;
use App\Models\SubscriptionStaffDetails;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Database\Seeder;

class TestSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SubscriptionService $subscriptionService): void
    {
        $user = User::find(17);
        $worker = User::find(10);
        $this->createSubscription($subscriptionService, $user, $worker);
    }

    private function createSubscription(
        SubscriptionService $subscriptionService,
        User $user,
        User $worker,
    ) {

        // Home Cleaning every monday at 8:00 am
        $subscription1 = $user->subscriptions()->create([
            'team_id' => $worker->teams->first()->id,
            'service_id' => 1,
            'frequency' => 1,
            'quarters' => 1,
            'start_at' => '2023-06-26',
            'start_time_at' => '8:00:00',
            'end_time_at' => '10:00:00',
            'quarters' => 8,
            'refill_sequence' => 52,
            'is_paused' => 0,
            'customer_id' => $user->customers->first()->id,
            'property_id' => $user->properties->first()->id,
            'property_type_id' => 1,
        ]);
        $this->createSubscriptionProduct($subscription1);
        $this->createSubscriptionStaff($subscription1, $user);

        // Home Cleaning every tuesday at 8:00 am
        $subscription2 = $user->subscriptions()->create([
            'team_id' => $worker->teams->first()->id,
            'service_id' => 1,
            'frequency' => 1,
            'quarters' => 1,
            'start_at' => '2023-06-27',
            'start_time_at' => '8:00:00',
            'end_time_at' => '10:00:00',
            'quarters' => 8,
            'refill_sequence' => 52,
            'is_paused' => 0,
            'customer_id' => $user->customers->first()->id,
            'property_id' => $user->properties->first()->id,
            'property_type_id' => 1,
        ]);
        $this->createSubscriptionProduct($subscription2);
        $this->createSubscriptionStaff($subscription2, $user);

        // Garden Cleaning every tuesday at 8:00 am
        $subscription3 = $user->subscriptions()->create([
            'team_id' => $worker->teams->first()->id,
            'service_id' => 2,
            'frequency' => 1,
            'quarters' => 1,
            'start_at' => '2023-06-27',
            'start_time_at' => '10:00:00',
            'end_time_at' => '12:00:00',
            'quarters' => 8,
            'refill_sequence' => 52,
            'is_paused' => 0,
            'customer_id' => $user->customers->first()->id,
            'property_id' => $user->properties->first()->id,
            'property_type_id' => 1,
        ]);
        $this->createSubscriptionProduct($subscription3);
        $this->createSubscriptionStaff($subscription3, $user);
        $subscriptionService->createInitialSchedules($subscription1, 0);
        $subscriptionService->createInitialSchedules($subscription2, 0);
        $subscriptionService->createInitialSchedules($subscription3, 0);
    }

    private function createSubscriptionStaff(Subscription $subscription, User $user)
    {
        SubscriptionStaffDetails::create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'quarters' => $subscription->quarters,
        ]);
    }

    private function createSubscriptionProduct(Subscription $subscription)
    {
        //get two rundom products where service id is equal to subscription service id
        $products = Product::inRandomOrder()
            ->where('service_id', $subscription->service_id)->take(2)->get();

        foreach ($products as $product) {
            SubscriptionProduct::create([
                'subscription_id' => $subscription->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }
    }
}
