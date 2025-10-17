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
        Schema::create('schedule_store_details', function (Blueprint $table) {
            $table->id();
            $table->integer('schedule_store_id');
            $table->dateTime('begins_at_changed')->nullable();
            $table->dateTime('ends_at_changed')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_store_details');
    }
};
