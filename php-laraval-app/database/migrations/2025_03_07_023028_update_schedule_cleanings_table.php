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
        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->change();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('property_id')->nullable()->change();
            $table->unsignedBigInteger('subscription_id')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->smallInteger('quarters')->nullable()->change();
            $table->boolean('is_fixed')->nullable()->change();
            $table->foreignId('laundry_order_id')->nullable()->after('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('laundry_type')->nullable()->index()->after('laundry_order_id');
        });

        // For timestamp columns, use raw SQL statements instead
        DB::statement('ALTER TABLE schedule_cleanings MODIFY start_at TIMESTAMP NULL');
        DB::statement('ALTER TABLE schedule_cleanings MODIFY end_at TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable(false)->change();
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->unsignedBigInteger('property_id')->nullable(false)->change();
            $table->unsignedBigInteger('subscription_id')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->smallInteger('quarters')->nullable(false)->change();
            $table->boolean('is_fixed')->nullable(false)->change();
        });

        // Handle timestamp columns with raw SQL
        DB::statement('ALTER TABLE schedule_cleanings MODIFY start_at TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE schedule_cleanings MODIFY end_at TIMESTAMP NOT NULL');
    }
};
