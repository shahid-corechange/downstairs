<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE schedule_employees CHANGE work_hour_id work_hour_id bigint unsigned NULL AFTER scheduleable_id;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
