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
        Schema::table('deviations', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('schedule_cleaning_id')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deviations', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
            $table->unsignedBigInteger('schedule_cleaning_id')->nullable(false)->change();
        });
    }
};
