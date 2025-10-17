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
        DB::statement('
            INSERT INTO translations (translationable_type, translationable_id, `key`, sv_SE, created_at, updated_at)
            SELECT "App\\Models\\CustomTask", ct.id, "name", ct.name, NOW(), NOW()
            FROM custom_tasks ct
            UNION
            SELECT "App\\Models\\CustomTask", ct.id, "description", ct.description, NOW(), NOW()
            FROM custom_tasks ct;
        ');

        Schema::table('custom_tasks', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            DELETE FROM translations
            WHERE translationable_type = "App\\Models\\CustomTask"
        ');

        Schema::table('custom_tasks', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
        });
    }
};
