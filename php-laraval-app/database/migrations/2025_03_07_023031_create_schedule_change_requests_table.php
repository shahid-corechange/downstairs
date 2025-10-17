<?php

use App\Enums\Schedule\ScheduleChangeStatusEnum;
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
        Schema::create('schedule_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('causer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('original_start_at')->nullable();
            $table->timestamp('start_at_changed');
            $table->timestamp('original_end_at')->nullable();
            $table->timestamp('end_at_changed');
            $table->string('status')->default(ScheduleChangeStatusEnum::Pending());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_change_requests');
    }
};
