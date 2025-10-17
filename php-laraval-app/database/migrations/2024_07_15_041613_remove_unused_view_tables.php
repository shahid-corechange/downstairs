<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS worked_hours;');
        DB::statement('DROP VIEW IF EXISTS product_summations;');
        DB::statement('DROP VIEW IF EXISTS service_summations;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
