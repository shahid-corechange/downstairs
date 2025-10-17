<?php

use App\Models\SubscriptionProduct;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $subscriptionProducts = SubscriptionProduct::with('product')->get();

        foreach ($subscriptionProducts as $subscriptionProduct) {
            DB::table('subscription_items')->insert([
                'subscription_id' => $subscriptionProduct->subscription_id,
                'itemable_id' => $subscriptionProduct->product->addon_id,
                'itemable_type' => 'App\\Models\\Addon',
                'quantity' => $subscriptionProduct->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // drop table subscription products
        Schema::dropIfExists('subscription_product');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
