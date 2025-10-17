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
                `wh`.`user_id` AS `user_id`,
                `e`.`fortnox_id` AS `fortnox_id`,
                TRIM(CONCAT_WS(' ', `u`.`first_name`, `u`.`last_name`)) AS `fullname`,
                MONTH(`wh`.`date`) AS `month`,
                YEAR(`wh`.`date`) AS `year`,
                SUM((CEILING((TIMESTAMPDIFF(MINUTE, CONCAT(`wh`.`date`, ' ', `wh`.`start_time`), CONCAT(`wh`.`date`, ' ', `wh`.`end_time`)) / 15)) / 4)) AS `total_work_hours`,
                SUM(COALESCE(`adjustment_data`.`adjustment_hours`, 0)) AS `adjustment_hours`,
                COALESCE((
                    SELECT SUM(CEILING(TIMESTAMPDIFF(MINUTE, se.start_at, se.end_at) / 15) / 4)
                    FROM `schedule_employees` se
                    WHERE se.user_id = `wh`.`user_id`
                    AND se.work_hour_id IS NOT NULL
                    AND (
                        (YEAR(se.start_at) = YEAR(`wh`.`date`) AND MONTH(se.start_at) = MONTH(`wh`.`date`))
                        OR (YEAR(se.end_at) = YEAR(`wh`.`date`) AND MONTH(se.end_at) = MONTH(`wh`.`date`))
                    )
                ), 0) AS `booking_hours`,
                COALESCE((
                    SELECT COUNT(*)
                    FROM `schedule_cleaning_deviations` scd
                    INNER JOIN `schedule_cleanings` sc ON sc.id = scd.schedule_cleaning_id
                    INNER JOIN `schedule_employees` se ON se.scheduleable_id = sc.id 
                        AND se.scheduleable_type = 'App\\Models\\ScheduleCleaning'
                    WHERE se.user_id = `wh`.`user_id`
                    AND sc.start_at >= DATE_FORMAT(CONCAT(YEAR(`wh`.`date`), '-', MONTH(`wh`.`date`), '-01'), '%Y-%m-%d 00:00:00')
                    AND sc.end_at <= LAST_DAY(CONCAT(YEAR(`wh`.`date`), '-', MONTH(`wh`.`date`), '-01 23:59:59'))
                ), 0) AS `schedule_cleaning_deviation`,
                COALESCE((
                    SELECT COUNT(*)
                    FROM `deviations` d
                    INNER JOIN `schedule_cleanings` sc ON sc.id = d.schedule_cleaning_id
                    WHERE d.user_id = `wh`.`user_id`
                    AND sc.start_at >= DATE_FORMAT(CONCAT(YEAR(`wh`.`date`), '-', MONTH(`wh`.`date`), '-01'), '%Y-%m-%d 00:00:00')
                    AND sc.end_at <= LAST_DAY(CONCAT(YEAR(`wh`.`date`), '-', MONTH(`wh`.`date`), '-01 23:59:59'))
                ), 0) AS `schedule_employee_deviation`
            FROM
                (((`downstairs`.`work_hours` `wh`
            LEFT JOIN `downstairs`.`users` `u` ON
                (`wh`.`user_id` = `u`.`id`))
            LEFT JOIN `downstairs`.`employees` `e` ON
                (`e`.`user_id` = `u`.`id`))
            LEFT JOIN (
                SELECT
                    `se`.`work_hour_id` AS `work_hour_id`,
                    (SUM(`ta`.`quarters`) / 4) AS `adjustment_hours`
                FROM
                    (`downstairs`.`schedule_employees` `se`
                LEFT JOIN `downstairs`.`time_adjustments` `ta` ON
                    (`ta`.`schedule_employee_id` = `se`.`id`))
                GROUP BY
                    `se`.`work_hour_id`) `adjustment_data` ON
                (`adjustment_data`.`work_hour_id` = `wh`.`id`))
            GROUP BY
                `wh`.`user_id`,
                `e`.`fortnox_id`,
                `fullname`,
                `year`,
                `month`,
                `booking_hours`,
                `schedule_cleaning_deviation`,
                `schedule_employee_deviation`;
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
