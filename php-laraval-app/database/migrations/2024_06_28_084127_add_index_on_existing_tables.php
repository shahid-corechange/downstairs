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
        Schema::table('users', function (Blueprint $table) {
            $table->index('cellphone');
        });

        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->index('end_at');
        });

        Schema::table('credits', function (Blueprint $table) {
            $table->index('valid_until');
        });

        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('translations', function (Blueprint $table) {
            $table->index('key');
        });

        Schema::table('schedule_employees', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('deviations', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('customer_discounts', function (Blueprint $table) {
            $table->index('type');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('created_at');
        });

        Schema::table('fixed_price_rows', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->index('membership_type');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('membership_type');
            $table->index('type');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('type');
            $table->index('month');
            $table->index('year');
            $table->index('status');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('created_at');
        });
    }
};
