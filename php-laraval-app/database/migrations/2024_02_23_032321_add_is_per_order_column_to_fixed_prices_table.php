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
            $table->boolean('is_per_order')->default(false)->after('apply_to_all_subscriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->dropColumn('is_per_order');
        });
    }
};
