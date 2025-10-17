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
        Schema::table('order_fixed_prices', function (Blueprint $table) {
            $table->foreignId('fixed_price_id')->after('id')->nullable()
                ->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fixed_prices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fixed_price_id');
        });
    }
};
