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
        Schema::table('customer_discounts', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()
                ->constrained()->cascadeOnDelete();
        });

        DB::statement('
            UPDATE customer_discounts, customer_user
            SET customer_discounts.user_id = customer_user.user_id
            WHERE customer_discounts.customer_id = customer_user.customer_id
            AND customer_discounts.user_id IS NULL
        ');

        Schema::table('customer_discounts', function (Blueprint $table) {
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
        Schema::table('customer_discounts', function (Blueprint $table) {
            //
        });
    }
};
