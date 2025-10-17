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
        Schema::create('cashier_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_hour_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('check_in_at');
            $table->foreignId('check_in_causer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('check_out_at')->nullable();
            $table->foreignId('check_out_causer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_attendances');
    }
};
