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
                    schedule_id,
                    user_id,
                    work_hour_id
                FROM
                    schedule_employees
                WHERE
                    deleted_at IS NULL
            ),
            filtered_schedules AS (
                SELECT
                    s.id AS schedule_id,
                    fse.user_id,
                    fse.work_hour_id,
                    s.start_at,
                    s.end_at
                FROM
                    schedules s
                JOIN filtered_schedule_employees fse ON s.id = fse.schedule_id  
            ),
            deviation_data AS(
            SELECT
                fs.user_id,
                YEAR(fs.start_at) AS `year`,
                    MONTH(fs.start_at) AS `month`,
                    COUNT(*) AS schedule_deviation
            FROM
                schedule_deviations sd
            JOIN filtered_schedules fs ON sd.schedule_id = fs.schedule_id
            WHERE
                sd.is_handled = 0
                AND sd.deleted_at IS null
            GROUP BY
                    fs.user_id,
                    `year`,
                    `month`
            ),
            employee_deviation_data AS (
            SELECT
                fs.user_id,
                YEAR(fs.start_at) AS `year`,
                MONTH(fs.start_at) AS `month`,
                COUNT(*) AS schedule_employee_deviation
            FROM
                deviations d
            JOIN filtered_schedules fs ON d.schedule_id = fs.schedule_id AND d.user_id = fs.user_id
            WHERE
                d.is_handled = 0
                AND d.deleted_at IS NULL
            GROUP BY
                    fs.user_id,
                    `year`,
                    `month`
            ),
            store_work_hours_data AS (
                SELECT
                    ca.user_id,
                    YEAR(ca.check_in_at) AS year,
                    MONTH(ca.check_in_at) AS month,
                    SUM(CEILING(TIMESTAMPDIFF(MINUTE, ca.check_in_at, ca.check_out_at) / 15) / 4) AS store_work_hours
                FROM
                    cashier_attendances ca
                WHERE
                    ca.deleted_at IS NULL
                    AND ca.check_in_at IS NOT NULL
                    AND ca.check_out_at IS NOT NULL
                GROUP BY
                    ca.user_id,
                    YEAR(ca.check_in_at),
                    MONTH(ca.check_in_at)
            ),
            schedule_work_hours_data AS (
                SELECT
                    wh.user_id,
                    YEAR(wh.date) AS year,
                    MONTH(wh.date) AS month,
                    SUM(CEILING(TIMESTAMPDIFF(MINUTE, CONCAT(wh.date, ' ', wh.start_time), CONCAT(wh.date, ' ', wh.end_time)) / 15) / 4) AS schedule_work_hours
                FROM
                    work_hours wh
                WHERE
                    wh.type = 'schedule'
                GROUP BY
                    wh.user_id,
                    YEAR(wh.date),
                    MONTH(wh.date)
            )
            SELECT
                wh.user_id,
                e.fortnox_id,
                e.id AS employee_id,
                TRIM(CONCAT_WS(' ', u.first_name, u.last_name)) AS fullname,
                MONTH(wh.date) AS month,
                YEAR(wh.date) AS year,
                COALESCE(SUM(ad.adjustment_hours), 0) AS adjustment_hours,
                COALESCE(MAX(bh.booking_hours), 0) AS booking_hours,
                COALESCE(MAX(sh.schedule_work_hours), 0) AS schedule_work_hours,
                COALESCE(MAX(swh.store_work_hours), 0) AS store_work_hours,
                COALESCE(MAX(sh.schedule_work_hours), 0) + COALESCE(MAX(swh.store_work_hours), 0) AS total_work_hours,
                COALESCE(MAX(dd.schedule_deviation), 0) AS schedule_deviation,
                COALESCE(MAX(ed.schedule_employee_deviation), 0) AS schedule_employee_deviation
            FROM
                work_hours wh
            LEFT JOIN users u ON wh.user_id = u.id
            LEFT JOIN employees e ON e.user_id = u.id
            LEFT JOIN schedule_work_hours_data sh ON 
                sh.user_id = wh.user_id 
                AND sh.year = YEAR(wh.date) 
                AND sh.month = MONTH(wh.date)
            LEFT JOIN adjustment_data ad ON ad.work_hour_id = wh.id
            LEFT JOIN booking_hours_data bh ON bh.user_id = wh.user_id AND bh.year = YEAR(wh.date) AND bh.month = MONTH(wh.date)
            LEFT JOIN store_work_hours_data swh ON swh.user_id = wh.user_id AND swh.year = YEAR(wh.date) AND swh.month = MONTH(wh.date)
            LEFT JOIN deviation_data dd ON dd.user_id = wh.user_id AND dd.year = YEAR(wh.date) AND dd.month = MONTH(wh.date)
            LEFT JOIN employee_deviation_data ed ON ed.user_id = wh.user_id AND ed.year = YEAR(wh.date) AND ed.month = MONTH(wh.date)
            GROUP BY
                wh.user_id,
                e.fortnox_id,
                e.id,
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
