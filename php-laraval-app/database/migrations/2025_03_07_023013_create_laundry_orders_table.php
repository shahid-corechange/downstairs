<?php

use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laundry_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('causer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('laundry_preference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pickup_property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignId('pickup_team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->time('pickup_time')->nullable();
            $table->foreignId('delivery_property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignId('delivery_team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->time('delivery_time')->nullable();
            $table->string('status')->default(LaundryOrderStatusEnum::Pending());
            $table->string('payment_method')->nullable();
            $table->timestamp('ordered_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_orders');
    }
};
