<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            INSERT INTO schedule_cleaning_tasks (custom_task_id, schedule_cleaning_id, is_completed)
            SELECT t.custom_task_id, se.scheduleable_id, MAX(t.is_completed)
            FROM tasks t, schedule_employees se
            WHERE t.schedule_employee_id = se.id
            GROUP BY t.custom_task_id, se.scheduleable_id;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // do nothing
    }
};
