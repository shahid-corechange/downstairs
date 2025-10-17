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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()
                ->constrained()->cascadeOnDelete();
        });

        DB::statement('
            UPDATE invoices, customer_user
            SET invoices.user_id = customer_user.user_id
            WHERE invoices.customer_id = customer_user.customer_id
            AND invoices.user_id IS NULL
        ');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
