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
            $table->dropColumn('scheduleable_id');
            $table->dropColumn('scheduleable_type');
            $table->unsignedBigInteger('schedule_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('scheduleable_id')->nullable();
            $table->string('scheduleable_type')->nullable();
            $table->unsignedBigInteger('schedule_id')->nullable()->change();
        });
    }
};
