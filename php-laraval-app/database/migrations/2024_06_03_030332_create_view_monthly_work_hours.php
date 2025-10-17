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
                MONTH(wh.`date`) AS month,
                YEAR(wh.`date`) AS year,
                SUM(
                    CEIL(
                        TIMESTAMPDIFF(
                            MINUTE , 
                            CONCAT(wh.`date`, ' ', wh.start_time), CONCAT(`date`, ' ', wh.end_time)
                        ) / 15
                    ) / 4
                ) AS total_work_hours
            FROM
                work_hours wh
            LEFT JOIN 
                users u on wh.user_id = u.id
            LEFT JOIN 
                employees e  on e.user_id = u.id
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
