<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW monthly_work_hours AS
            SELECT
                wh.user_id,
                e.fortnox_id,
                TRIM(CONCAT_WS(' ', u.first_name, u.last_name)) AS fullname,
                MONTH(wh.date) AS month,
                YEAR(wh.date) AS year,
                SUM(
                    CEIL(
                        TIMESTAMPDIFF(
                            MINUTE, 
                            CONCAT(wh.date, ' ', wh.start_time), 
                            CONCAT(wh.date, ' ', wh.end_time)
                        ) / 15
                    ) / 4
                ) AS total_work_hours,
                SUM(COALESCE(adjustment_data.adjustment_hours, 0)) AS adjustment_hours
            FROM
                work_hours wh
            LEFT JOIN 
                users u ON wh.user_id = u.id
            LEFT JOIN 
                employees e ON e.user_id = u.id
            LEFT JOIN 
                (
                    SELECT
                        se.work_hour_id,
                        SUM(ta.quarters) / 4 AS adjustment_hours
                    FROM
                        schedule_employees se
                    LEFT JOIN 
                        time_adjustments ta ON ta.schedule_employee_id = se.id
                    GROUP BY 
                        se.work_hour_id
                ) AS adjustment_data ON adjustment_data.work_hour_id = wh.id
            GROUP BY
                wh.user_id, e.fortnox_id, year, month;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS monthly_work_hours;');
    }
};
