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
        Schema::table('unassign_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
            $table->dropColumn('start_time_at');
            $table->dropColumn('quarters');
            $table->dropColumn('refill_sequence');
            $table->dropColumn('product_ids');
            $table->json('addon_ids')->nullable();
            $table->json('product_carts')->nullable();
            $table->json('cleaning_detail')->nullable();
            $table->json('laundry_detail')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unassign_subscriptions', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->time('start_time_at');
            $table->smallInteger('quarters');
            $table->smallInteger('refill_sequence')->default(12);
            $table->dropColumn('addon_ids');
            $table->dropColumn('product_carts');
            $table->dropColumn('cleaning_detail');
            $table->dropColumn('laundry_detail');
            $table->dropColumn('product_ids');
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
