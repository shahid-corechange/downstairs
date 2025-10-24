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
        Schema::dropIfExists('schedule_cleaning_change_requests');
        Schema::dropIfExists('schedule_cleaning_products');
        Schema::dropIfExists('schedule_cleaning_tasks');
        Schema::dropIfExists('schedule_cleaning_deviations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('schedule_cleaning_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_cleaning_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('schedule_cleaning_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_cleaning_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('schedule_cleaning_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_cleaning_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('schedule_cleaning_deviations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_cleaning_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
