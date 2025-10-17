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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('subscribable_type')->after('fixed_price_id');
            $table->unsignedBigInteger('subscribable_id')->after('subscribable_type');
            $table->index(['subscribable_id', 'subscribable_type']);
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('team_id')->nullable()->change();
            $table->unsignedBigInteger('property_id')->nullable()->change();
            $table->smallInteger('refill_sequence')->nullable()->change();
            $table->smallInteger('quarters')->nullable()->change();
        });

        // For timestamp columns, use raw SQL statements instead
        DB::statement('ALTER TABLE subscriptions MODIFY start_time_at TIMESTAMP NULL');
        DB::statement('ALTER TABLE subscriptions MODIFY end_time_at TIMESTAMP NULL');

        // Drop table subscription_details
        Schema::dropIfExists('subscription_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['subscribable_type']);
            $table->dropIndex(['subscribable_id']);
            $table->dropColumn('subscribable_type');
            $table->dropColumn('subscribable_id');

            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->unsignedBigInteger('team_id')->nullable(false)->change();
            $table->unsignedBigInteger('property_id')->nullable(false)->change();
            $table->smallInteger('refill_sequence')->nullable(false)->change();
        });

        // For timestamp columns, use raw SQL statements instead
        DB::statement('ALTER TABLE subscriptions MODIFY start_time_at TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE subscriptions MODIFY end_time_at TIMESTAMP NOT NULL');

        // Create table subscription_details
        Schema::create('subscription_details', function (Blueprint $table) {
            $table->id();
            $table->integer('subscription_id');
            $table->integer('squarefeet');
            $table->decimal('price_per_quarters');
            $table->decimal('price_per_squarefeet');
            $table->decimal('price_material');
            $table->decimal('price_establish');
            $table->integer('vat_id')->default(25); // ENUM: 0, 6, 12, 25
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
