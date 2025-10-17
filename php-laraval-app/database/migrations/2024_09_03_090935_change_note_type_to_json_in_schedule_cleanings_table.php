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
            $table->renameColumn('note', 'note_old');
        });

        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->json('note')->after('key_information')->nullable();
        });

        DB::statement("
            UPDATE schedule_cleanings sc
            INNER JOIN subscriptions s ON s.id = sc.subscription_id
            LEFT JOIN meta m ON m.metable_id = sc.property_id
            AND m.metable_type = 'App\\\\Models\\\\Property'
            AND m.`key` = 'note'
            SET sc.note = IF(
                m.value IS NULL AND s.description IS NULL,
                JSON_OBJECT(),
                JSON_OBJECT(
                    'property_note', m.value, 
                    'subscription_note', s.description
                )
            );
        ");

        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->dropColumn('note_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
