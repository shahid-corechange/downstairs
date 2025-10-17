<?php

use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
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
        Schema::create('schedule_cleaning_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_cleaning_id')->constrained()->cascadeOnDelete();
            $table->integer('quarters_changed')->nullable();
            $table->decimal('squarefeet_changed')->nullable();
            $table->timestamp('start_at_changed')->nullable();
            $table->timestamp('end_at_changed')->nullable();
            $table->string('status')->default(ScheduleCleaningChangeStatusEnum::Pending());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_cleaning_change_requests');
    }
};
