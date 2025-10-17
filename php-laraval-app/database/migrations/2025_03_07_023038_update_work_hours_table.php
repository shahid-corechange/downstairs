<?php

use App\Enums\WorkHour\WorkHourTypeEnum;
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
        Schema::table('work_hours', function (Blueprint $table) {
            $table->string('type')->default(WorkHourTypeEnum::Schedule())->after('fortnox_attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_hours', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
