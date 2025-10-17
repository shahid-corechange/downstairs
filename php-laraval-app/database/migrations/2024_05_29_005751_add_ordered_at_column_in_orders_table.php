<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('ordered_at')->nullable()->after('paid_at');
        });

        DB::statement('
            UPDATE orders o, schedule_cleanings sc SET o.ordered_at = sc.start_at 
            WHERE o.orderable_id = sc.id;
        ');

        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('ordered_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('ordered_at');
        });
    }
};
