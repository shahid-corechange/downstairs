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
        Schema::create('old_orders', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->constrained('orders', 'id')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('old_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_orders');
    }
};
