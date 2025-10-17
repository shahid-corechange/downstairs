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
            $table->foreignId('issuer_id')->after('schedule_cleaning_id')->nullable()
                ->constrained('users')->cascadeOnDelete();
            $table->text('description')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credits', function (Blueprint $table) {
            $table->dropForeign(['issuer_id']);
            $table->dropColumn('issuer_id');
            $table->dropColumn('description');
        });
    }
};
