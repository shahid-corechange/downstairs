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
        // Create temporary table with old_orders data
        Schema::create('old_orders_temp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_order_id');
        });

        // Copy old_orders data to temporary table
        DB::statement('
            INSERT INTO old_orders_temp (old_order_id)
            SELECT DISTINCT old_order_id
            FROM old_orders
        ');

        // Drop old_orders table
        Schema::dropIfExists('old_orders');

        // Rename temporary table to old_orders
        Schema::rename('old_orders_temp', 'old_orders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
