<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign keys if they exist (constraint names may differ across environments)
        $this->dropForeignIfExists('subscriptions', 'team_id');
        $this->dropForeignIfExists('subscriptions', 'property_id');

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'team_id')) {
                $table->dropColumn('team_id');
            }

            if (Schema::hasColumn('subscriptions', 'property_id')) {
                $table->dropColumn('property_id');
            }

            foreach (['start_time_at', 'end_time_at', 'refill_sequence', 'quarters'] as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('start_time_at')->nullable();
            $table->timestamp('end_time_at')->nullable();
            $table->smallInteger('refill_sequence')->nullable();
            $table->smallInteger('quarters')->nullable();
        });
    }

    /**
     * Drop a foreign key for a given table/column if it exists.
     * Uses MySQL information_schema to locate the actual constraint name.
     */
    private function dropForeignIfExists(string $table, string $column): void
    {
        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        foreach ($constraints as $row) {
            $constraintName = is_array($row) ? $row['CONSTRAINT_NAME'] : $row->CONSTRAINT_NAME;
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $constraintName));
        }
    }
};
