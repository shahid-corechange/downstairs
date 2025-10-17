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
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->dropColumn('apply_to_all_subscriptions');
        });

        Schema::table('order_fixed_prices', function (Blueprint $table) {
            $table->dropColumn('apply_to_all_subscriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->boolean('apply_to_all_subscriptions')->default(false);
        });

        Schema::table('order_fixed_prices', function (Blueprint $table) {
            $table->boolean('apply_to_all_subscriptions')->default(false);
        });
    }
};
