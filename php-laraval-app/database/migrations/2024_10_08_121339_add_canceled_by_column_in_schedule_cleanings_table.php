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
        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $name = 'cancelable';
            $indexName = 'schedule_cleanings_cancelable_index';

            $table->unsignedBigInteger("{$name}_id")->nullable()->after('note');
            $table->string("{$name}_type")->nullable()->after('note');
            $table->index(["{$name}_type", "{$name}_id"], $indexName);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->dropIndex('schedule_cleanings_cancelable_index');
            $table->dropColumn('cancelable_type');
            $table->dropColumn('cancelable_id');
        });
    }
};
