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
        Schema::table('schedule_cleaning_change_requests', function (Blueprint $table) {
            $table->dropColumn('quarters_changed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_cleaning_change_requests', function (Blueprint $table) {
            $table->integer('quarters_changed')->nullable();
        });
    }
};
