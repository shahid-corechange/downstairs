<?php

use App\Enums\PriceAdjustment\PriceAdjustmentRowStatusEnum;
use App\Enums\VatNumbersEnum;
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
        Schema::create('price_adjustment_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_adjustment_id')->constrained()->onDelete('cascade');
            $table->morphs('adjustable');
            $table->unsignedDecimal('previous_price');
            $table->unsignedDecimal('price');
            $table->unsignedTinyInteger('vat_group')->default(VatNumbersEnum::TwentyFive());
            $table->string('status')->default(PriceAdjustmentRowStatusEnum::Pending());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_adjustment_rows');
    }
};
