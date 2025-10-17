<?php

use App\Models\ScheduleCleaning;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $canceledScheduleCleanings = ScheduleCleaning::canceled()->get();

        foreach ($canceledScheduleCleanings as $scheduleCleaning) {
            $scheduleCleaning->update([
                'canceled_at' => $scheduleCleaning->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
