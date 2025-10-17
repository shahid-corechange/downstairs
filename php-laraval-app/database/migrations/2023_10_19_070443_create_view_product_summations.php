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
            {$statement} VIEW product_summations AS
            SELECT 
                DATE(sc.start_at) AS date, 
                scp.name, 
                SUM(scp.quantity) AS total_quantity
            FROM 
                schedule_cleaning_products scp
            LEFT JOIN 
                schedule_cleanings sc ON scp.schedule_cleaning_id  = sc.id
            WHERE 
                sc.status = 'done' 
            GROUP BY 
                DATE(sc.start_at), 
                scp.name 
            ORDER BY 
                date;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS product_summations;');
    }
};
