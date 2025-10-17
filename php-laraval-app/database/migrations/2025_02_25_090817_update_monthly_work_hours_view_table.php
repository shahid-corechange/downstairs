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
            WITH adjustment_data AS (
                SELECT
                    se.work_hour_id AS work_hour_id,
                    SUM(ta.quarters) / 4 AS adjustment_hours
                FROM
                    schedule_employees se
                LEFT JOIN time_adjustments ta ON ta.schedule_employee_id = se.id
                GROUP BY
                    se.work_hour_id
            ),
            booking_hours_data AS (
                SELECT
                    se.user_id,
                    YEAR(se.start_at) AS year,
                    MONTH(se.start_at) AS month,
                    SUM(CEILING(TIMESTAMPDIFF(SECOND, se.start_at, se.end_at) / 900) / 4) AS booking_hours
                FROM
                    schedule_employees se
                WHERE
                    se.work_hour_id IS NOT NULL
                GROUP BY
                    se.user_id,
                    YEAR(se.start_at),
                    MONTH(se.start_at)
            ),
            filtered_schedule_employees AS (
                SELECT
                    scheduleable_id AS schedule_cleaning_id,
                    user_id,
                    work_hour_id
                FROM
                    schedule_employees
                WHERE
                    scheduleable_type = 'App\\Models\\ScheduleCleaning'
                    AND deleted_at IS NULL
            ),
            filtered_schedule_cleanings AS (
                SELECT
                    sc.id AS schedule_cleaning_id,
                    fse.user_id,
                    fse.work_hour_id,
                    sc.start_at,
                    sc.end_at
                FROM
                    schedule_cleanings sc
                JOIN filtered_schedule_employees fse ON sc.id = fse.schedule_cleaning_id  
            ),
            cleaning_deviation_data AS(
            SELECT
                fsc.user_id,
                YEAR(fsc.start_at) AS `year`,
                    MONTH(fsc.start_at) AS `month`,
                    COUNT(*) AS schedule_cleaning_deviation
            FROM
                schedule_cleaning_deviations scd
            JOIN filtered_schedule_cleanings fsc ON scd.schedule_cleaning_id = fsc.schedule_cleaning_id
            WHERE
                scd.is_handled = 0
                AND scd.deleted_at IS null
            GROUP BY
                    fsc.user_id,
                    `year`,
                    `month`
            ),
            employee_deviation_data AS (
            SELECT
                fsc.user_id,
                YEAR(fsc.start_at) AS `year`,
                MONTH(fsc.start_at) AS `month`,
                COUNT(*) AS schedule_employee_deviation
            FROM
                deviations d
            JOIN filtered_schedule_cleanings fsc ON d.schedule_cleaning_id = fsc.schedule_cleaning_id AND d.user_id = fsc.user_id
            WHERE
                d.is_handled = 0
                AND d.deleted_at IS NULL
            GROUP BY
                    fsc.user_id,
                    `year`,
                    `month`
            )
            SELECT
                wh.user_id,
                e.fortnox_id,
                TRIM(CONCAT_WS(' ', u.first_name, u.last_name)) AS fullname,
                MONTH(wh.date) AS month,
                YEAR(wh.date) AS year,
                SUM(CEILING(TIMESTAMPDIFF(MINUTE, CONCAT(wh.date, ' ', wh.start_time), CONCAT(wh.date, ' ', wh.end_time)) / 15) / 4) AS total_work_hours,
                COALESCE(SUM(ad.adjustment_hours), 0) AS adjustment_hours,
                COALESCE(MAX(bh.booking_hours), 0) AS booking_hours,
                COALESCE(MAX(cd.schedule_cleaning_deviation), 0) AS schedule_cleaning_deviation,
                COALESCE(MAX(ed.schedule_employee_deviation), 0) AS schedule_employee_deviation
            FROM
                work_hours wh
            LEFT JOIN users u ON wh.user_id = u.id
            LEFT JOIN employees e ON e.user_id = u.id
            LEFT JOIN adjustment_data ad ON ad.work_hour_id = wh.id
            LEFT JOIN booking_hours_data bh ON bh.user_id = wh.user_id AND bh.year = YEAR(wh.date) AND bh.month = MONTH(wh.date)
            LEFT JOIN cleaning_deviation_data cd ON cd.user_id = wh.user_id AND cd.year = YEAR(wh.date) AND cd.month = MONTH(wh.date)
            LEFT JOIN employee_deviation_data ed ON ed.user_id = wh.user_id AND ed.year = YEAR(wh.date) AND ed.month = MONTH(wh.date)
            GROUP BY
                wh.user_id,
                e.fortnox_id,
                fullname,
                YEAR(wh.date),
                MONTH(wh.date);
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
