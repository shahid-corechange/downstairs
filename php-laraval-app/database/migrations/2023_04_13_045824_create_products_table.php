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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('fortnox_article_id')->nullable();
            $table->foreignId('category_id')->nullable()
                ->constrained('product_categories', 'id')->cascadeOnDelete();
            $table->string('unit')->nullable();
            $table->unsignedDecimal('price');
            $table->unsignedSmallInteger('credit_price')->nullable();
            $table->unsignedTinyInteger('vat_group')->default(VatNumbersEnum::TwentyFive());
            $table->boolean('has_rut');
            $table->boolean('in_app');
            $table->boolean('in_store');
            $table->text('thumbnail_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
