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
        Schema::table('credits', function (Blueprint $table) {
            $table->unsignedTinyInteger('initial_amount')->after('user_id');
            $table->unsignedTinyInteger('remaining_amount')->after('initial_amount');
            $table->string('type')->after('remaining_amount');
            $table->foreignId('schedule_cleaning_id')->after('user_id')->nullable()
                ->constrained()->cascadeOnDelete();
        });

        Schema::create('credit_credit_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_transaction_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('amount');
        });

        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            $table->renameColumn('amount', 'total_amount');
            $table->unsignedBigInteger('schedule_cleaning_id')->nullable()->change();
            $table->foreignId('issuer_id')->after('schedule_cleaning_id')->nullable()
                ->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credits', function (Blueprint $table) {
            $table->dropColumn('initial_amount');
            $table->dropColumn('remaining_amount');
            $table->dropColumn('type');
            $table->dropForeign(['schedule_cleaning_id']);
            $table->dropColumn('schedule_cleaning_id');
        });

        Schema::dropIfExists('credit_credit_transactions');

        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'schedule_cleaning_id', 'issuer_id']);
            $table->dropColumn('user_id');
            $table->renameColumn('total_amount', 'amount');
            $table->foreignId('schedule_cleaning_id')->constrained()->cascadeOnDelete()->change();
            $table->dropColumn('issuer_id');
        });
    }
};
