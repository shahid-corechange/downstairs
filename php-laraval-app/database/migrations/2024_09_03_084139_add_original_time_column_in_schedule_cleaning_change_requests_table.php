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
        Schema::table('schedule_cleaning_change_requests', function (Blueprint $table) {
            $table->dropColumn('squarefeet_changed');
            $table->string('original_start_at')->after('schedule_cleaning_id')->nullable();
            $table->string('original_end_at')->after('start_at_changed')->nullable();
            $table->unsignedBigInteger('causer_id')->after('schedule_cleaning_id')->nullable();
            $table->foreign('causer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_cleaning_change_requests', function (Blueprint $table) {
            $table->decimal('squarefeet_changed')->after('schedule_cleaning_id')->nullable();
            $table->dropColumn('original_start_at');
            $table->dropColumn('original_end_at');
            $table->dropForeign(['causer_id']);
            $table->dropColumn('causer_id');
        });
    }
};
