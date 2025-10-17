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
        Schema::table('schedule_employees', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('scheduleable_id')->nullable()->change();
            $table->string('scheduleable_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_employees', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
            $table->unsignedBigInteger('scheduleable_id')->nullable(false)->change();
            $table->string('scheduleable_type')->nullable(false)->change();
        });
    }
};
