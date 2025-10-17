<?php

use App\Enums\Schedule\ScheduleStatusEnum;
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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->cascadeOnDelete();
            $table->morphs('scheduleable');
            $table->string('status')->default(ScheduleStatusEnum::Booked())->index();
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at');
            $table->timestamp('original_start_at')->nullable()->index();
            $table->smallInteger('quarters');
            $table->boolean('is_fixed')->default(0);
            $table->text('key_information')->nullable();
            $table->text('note')->nullable();
            $table->string('cancelable_type')->nullable()->index();
            $table->unsignedBigInteger('cancelable_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('canceled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
