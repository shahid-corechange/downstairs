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
        DB::statement('
            UPDATE credit_transactions
            JOIN credits ON credit_transactions.credit_id = credits.id
            SET credit_transactions.user_id = credits.user_id
            WHERE credit_transactions.user_id IS NULL
        ');

        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropForeign(['credit_id']);
            $table->dropColumn('credit_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->foreignId('credit_id')->after('id')->constrained()->cascadeOnDelete();
        });
    }
};
