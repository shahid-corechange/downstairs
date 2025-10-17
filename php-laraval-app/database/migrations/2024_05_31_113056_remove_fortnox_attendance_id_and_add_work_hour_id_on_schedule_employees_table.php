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
        Schema::table('schedule_employees', function (Blueprint $table) {
            $table->dropColumn('fortnox_attendance_id');
            $table->foreignId('work_hour_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_employees', function (Blueprint $table) {
            $table->string('fortnox_attendance_id')->nullable();
            $table->dropConstrainedForeignId('work_hour_id');
        });
    }
};
