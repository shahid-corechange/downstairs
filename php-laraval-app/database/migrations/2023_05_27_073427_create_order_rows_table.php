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
        Schema::create('order_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('fortnox_article_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->unsignedDecimal('price');
            $table->unsignedTinyInteger('discount_percentage')->default(0);
            $table->smallInteger('vat')->default(VatNumbersEnum::TwentyFive());
            $table->boolean('has_rut');
            $table->text('internal_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_rows');
    }
};
