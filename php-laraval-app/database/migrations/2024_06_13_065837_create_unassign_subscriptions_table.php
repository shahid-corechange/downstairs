<?php

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
        Schema::create('unassign_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('frequency'); // SubscriptionFrequencyEnum
            $table->date('start_at');
            $table->date('end_at')->nullable();
            $table->time('start_time_at');
            $table->smallInteger('quarters');
            $table->smallInteger('refill_sequence')->default(12);
            $table->boolean('is_fixed')->default(0);
            $table->text('description')->nullable();
            $table->unsignedDecimal('fixed_price')->nullable();
            $table->json('product_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unassign_subscriptions');
    }
};
