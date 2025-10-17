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
            $table->foreignId('user_id')->after('id')->nullable()
                ->constrained()->cascadeOnDelete();
        });

        DB::statement('
            UPDATE fixed_prices, customer_user
            SET fixed_prices.user_id = customer_user.user_id
            WHERE fixed_prices.customer_id = customer_user.customer_id
            AND fixed_prices.user_id IS NULL
        ');

        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('customer_id')->after('id')->constrained()
                ->cascadeOnDelete();
        });
    }
};
