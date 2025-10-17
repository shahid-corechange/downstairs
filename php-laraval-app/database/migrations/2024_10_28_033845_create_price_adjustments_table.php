<?php

use App\Enums\PriceAdjustment\PriceAdjustmentStatusEnum;
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
        Schema::create('price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('causer_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('price_type');
            $table->decimal('price');
            $table->date('execution_date');
            $table->string('status')->default(PriceAdjustmentStatusEnum::Pending());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_adjustments');
    }
};
