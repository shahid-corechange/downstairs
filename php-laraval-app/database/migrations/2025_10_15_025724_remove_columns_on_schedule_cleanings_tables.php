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
        // Drop foreign keys if they exist
        foreach (['team_id', 'customer_id', 'property_id', 'subscription_id'] as $fkColumn) {
            $this->dropForeignIfExists('schedule_cleanings', $fkColumn);
        }

        Schema::table('schedule_cleanings', function (Blueprint $table) {
            foreach ([
                'team_id', 'customer_id', 'property_id', 'subscription_id',
                'status', 'start_at', 'end_at', 'original_start_at',
                'quarters', 'is_fixed', 'key_information', 'note',
                'cancelable_type', 'cancelable_id', 'canceled_at', 'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('schedule_cleanings', $column)) {
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
        Schema::table('schedule_cleanings', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->nullable()->index();
            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('original_start_at')->nullable()->index();
            $table->smallInteger('quarters')->nullable();
            $table->boolean('is_fixed')->nullable()->default(0);
            $table->text('key_information')->nullable();
            $table->text('note')->nullable();
            $table->string('cancelable_type')->nullable();
            $table->unsignedBigInteger('cancelable_id')->nullable();
            $table->timestamp('canceled_at')->nullable();
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
