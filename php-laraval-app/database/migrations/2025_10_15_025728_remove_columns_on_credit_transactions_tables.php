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
        // Drop FK if present
        $this->dropForeignIfExists('credit_transactions', 'schedule_cleaning_id');

        Schema::table('credit_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('credit_transactions', 'schedule_cleaning_id')) {
                $table->dropColumn('schedule_cleaning_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->foreignId('schedule_cleaning_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Helper to drop a foreign key for a given table/column if it exists.
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
