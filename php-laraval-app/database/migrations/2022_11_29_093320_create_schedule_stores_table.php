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
        Schema::create('schedule_stores', function (Blueprint $table) {
            $table->id();
            $table->integer('district_id');
            $table->integer('user_id');
            $table->integer('contact_id');
            $table->string('status')->default('draft'); // ENUM: draft, pending, cancel, progress, done
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_stores');
    }
};
