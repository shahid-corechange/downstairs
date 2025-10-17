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
        Schema::table('rut_co_applicants', function (Blueprint $table) {
            $table->date('pause_start_date')->nullable()->after('dial_code');
            $table->date('pause_end_date')->nullable()->after('pause_start_date');
            $table->boolean('is_enabled')->default(false)->after('pause_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rut_co_applicants', function (Blueprint $table) {
            $table->dropColumn('pause_start_date');
            $table->dropColumn('pause_end_date');
            $table->dropColumn('is_enabled');
        });
    }
};
