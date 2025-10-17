<?php

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
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
        Schema::create('schedule_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('scheduleable', 'schedule_employeeableable_index');
            // start info
            $table->decimal('start_latitude', 10)->nullable();
            $table->decimal('start_longitude', 10)->nullable();
            $table->string('start_ip')->nullable();
            $table->timestamp('start_at')->nullable();
            // end info
            $table->decimal('end_latitude', 10)->nullable();
            $table->decimal('end_longitude', 10)->nullable();
            $table->string('end_ip')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default(ScheduleEmployeeStatusEnum::Pending());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_events');
    }
};
