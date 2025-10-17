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
            {$statement} VIEW service_summations AS
            SELECT 
                DATE(sc.start_at) AS date, 
                s.id, 
                SUM(p.square_meter) as total_square_meter
            FROM 
                schedule_cleanings sc
            LEFT JOIN 
                subscriptions sub ON sc.subscription_id = sub.id
            LEFT JOIN 
                services s ON sub.service_id  = s.id
            LEFT JOIN 
                properties p  ON sc.property_id  = p.id
            WHERE 
                sc.status = 'done' 
            GROUP BY 
                DATE(sc.start_at), 
                s.id
            ORDER BY 
                date;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS service_summations;');
    }
};
