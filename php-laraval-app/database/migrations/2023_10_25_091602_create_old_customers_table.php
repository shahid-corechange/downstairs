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
        Schema::create('old_customers', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->constrained('customers', 'id')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('old_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_customers');
    }
};
