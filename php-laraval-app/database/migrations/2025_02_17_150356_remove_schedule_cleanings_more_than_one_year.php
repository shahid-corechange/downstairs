<?php

use App\Models\ScheduleCleaning;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove schedule cleanings that has wrong start_at
        ScheduleCleaning::withTrashed()
            ->whereDate('start_at', '>=', '2025-10-01')
            ->forceDelete();

        // Remove schedule employees that that don't have schedule cleaning
        DB::statement('
            DELETE FROM schedule_employees
            WHERE NOT EXISTS (
                SELECT 1 
                FROM schedule_cleanings sc 
                WHERE sc.id = schedule_employees.scheduleable_id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
