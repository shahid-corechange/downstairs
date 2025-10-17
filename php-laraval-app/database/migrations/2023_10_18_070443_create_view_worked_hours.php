<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statement = 'CREATE OR REPLACE';

        DB::statement("
            {$statement} VIEW worked_hours AS
            SELECT 
                DATE(s.start_at) AS date, 
                SUM(sub.quarters) AS total_quarters, 
                SUM(sub.quarters) * 15 AS total_minutes
            FROM 
                schedule_cleanings s
            JOIN 
                subscriptions sub ON s.subscription_id = sub.id
            WHERE 
                s.status = 'done' 
            GROUP BY 
                DATE(s.start_at)
            ORDER BY 
                date;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS worked_hours;');
    }
};
