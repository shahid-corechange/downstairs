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
        Schema::create('time_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('causer_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('quarters');
            $table->string('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_adjustments');
    }
};
