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
        Schema::create('store_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('quantity');
            $table->unsignedDecimal('price');
            $table->unsignedTinyInteger('vat_group')->default(VatNumbersEnum::TwentyFive());
            $table->unsignedDecimal('discount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_sale_products');
    }
};
