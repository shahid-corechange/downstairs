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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_details');
    }
};
