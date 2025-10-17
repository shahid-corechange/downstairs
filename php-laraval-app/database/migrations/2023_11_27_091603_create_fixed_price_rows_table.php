<?php

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
        Schema::create('fixed_price_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_price_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('quantity');
            $table->unsignedDecimal('price');
            $table->unsignedTinyInteger('vat_group')->default(VatNumbersEnum::TwentyFive());
            $table->boolean('has_rut');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_price_rows');
    }
};
